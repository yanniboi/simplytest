<?php

namespace Drupal\simplytest_submission;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\devel\DevelDumperManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Service for handling communication with Service Server.
 */
class SubmissionService {

  use StringTranslationTrait;

  /**
   * The submission service request details.
   *
   * @var array
   */
  protected $settings;

  /**
   * The logger for the simplytest_submission channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The devel dumper manager.
   *
   * @var \Drupal\devel\DevelDumperManagerInterface
   */
  protected $dumper;

  /**
   * The entity for which the script is being built.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $buildEntity;

  /**
   * {@inheritdoc}
   */
  public function __construct(LoggerInterface $logger, Settings $settings, DevelDumperManagerInterface $dumper = NULL) {
    $this->logger = $logger;
    $this->dumper = $dumper;

    if (!empty($settings->get('simplytest_submission'))) {
      $this->settings = $settings->get('simplytest_submission');
    }
    elseif (!empty($token = \Drupal::state()->get('simplytest_submission.service_token'))) {
      $this->settings['service_token'] = $token;
    }
    else {
      $this->logger->error('No settings found for simplytest_submission');
    }
  }

  /**
   * Check to see if the service is active.
   *
   * Look to see if API settings are configured.
   *
   * @return bool
   *   True if service is active, false otherwise.
   */
  public function isActive() {
    if (!empty($this->settings)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Deploys submission instance on service server.
   *
   * @param SubmissionInterface $entity
   *   Entity being used to make request.
   * @param string $script
   *   Bash script to be used in deployment.
   *
   * @return array
   *   Data response from service server.
   *
   * @throws \Exception
   *   Throws an exception if server returns error.
   */
  public function buildInstance(SubmissionInterface $entity, $script) {
    // Request preparation.
    $url = SubmissionInterface::SERVICE_URL;
    $url .= '/?' . http_build_query([
      // Use $ drush sset simplytest_submission.service_token TOKEN.
      // @todo provide config interface for this / or do it within rules UI
      'token' => $this->settings['service_token'],
      'ttl' => $entity->instance_runtime->value,
      'image' => $entity->instance_image->value,
      'cache' => ($entity->instance_snapshot_cache->value) ? 'true' : 'false',
    ]);

    // Send request.
    $client = \Drupal::httpClient();
    $response = $client->request('POST', $url, [
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/octet-stream; charset=utf-8',
      ],
      'body' => $script,
    ]);

    // Check response.
    if ($response->getStatusCode() !== 201) {
      throw new \Exception('Invalid status code returned from spawn.sh api');
    }

    $data = json_decode($response->getBody(), TRUE);

    if (!isset($data['id'])) {
      throw new \Exception('No id in spawn.sh response data');
    }

    return $data;
  }

  /**
   * Builds a deploy script for a submission.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that the script is being build for.
   *
   * @return bool|array
   *   Deploy script for building submission instance.
   */
  public function buildScript(EntityInterface $entity) {
    $this->buildEntity = $entity;

    $script = [];
    try {
      // Break up script into stages.
      $script[] = $this->buildServer();
      $script[] = $this->buildDrupal();
      $script[] = $this->buildProject();
      $script[] = $this->buildTasks();

      if (!empty($script)) {
        return implode("\n", $script);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred: %error', ['%error' => $e->getMessage()]);
    }

    return FALSE;
  }

  /**
   * Builds the server part of the deploy script.
   *
   * @return bool|array
   *   Deploy script for building submission instance.
   *
   * @throws \Exception
   *   Throws an exception if there is an error in the build.
   */
  protected function buildServer() {
    $script = [];
    $script[] = '#!/bin/bash';
    $script[] = 'export DEBIAN_FRONTEND=noninteractive';
    $script[] = 'set -x';
    $script[] = 'echo "Starting next script - Build Server"';
    $script[] = 'apt-get update';
    $script[] = 'apt-get upgrade -y';
    // For some reason service startup is a lot faster without cloud-init.
    $script[] = 'sudo apt-get remove -y --auto-remove cloud-init';

    $packages = [];
    $packages_script = [];

    // General purpose packages.
    $packages[] = 'zip';

    // PHP.
    switch ($this->buildEntity->webspace_interpreter->value) {
      case 'php7-fpm':
        $packages[] = 'php7.0-fpm';
        break;

      case 'mod-php7':
        // Installed in the apache2 part.
        break;

      case 'php7-cgi':
        $packages[] = 'php7.0-cgi';
        break;
    }
    // General PHP modules that are required by drupal, drush or composer.
    $packages[] = 'php7.0-xml';
    $packages[] = 'php7.0-curl';
    $packages[] = 'php7.0-gd';
    $packages[] = 'php7.0-json';
    $packages[] = 'php7.0-mbstring';

    // DB.
    switch ($this->buildEntity->webspace_dbs->value) {
      case 'mariadb':
        $packages[] = 'mariadb-server';
        $packages[] = 'php7.0-mysql';
        // Prepare db mysql://drupal:drupal@localhost/drupal.
        $packages_script[] = 'mysql -u root -e "
        CREATE USER \'drupal\'@\'localhost\' IDENTIFIED BY \'drupal\';
        GRANT USAGE ON *.* TO \'drupal\'@\'localhost\' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
        CREATE DATABASE IF NOT EXISTS \`drupal\`;
        GRANT ALL PRIVILEGES ON \`drupal\`.* TO \'drupal\'@\'localhost\'"';
        // Gracefully shut down database before snapshotting.
        $packages_script[] = 'service mysql stop';
        // Skip mysql recovery for faster startup.
        $packages_script[] = 'truncate -s 0 /etc/mysql/debian-start';
        break;

      case 'mysql':
        $packages[] = 'mysql-server';
        $packages[] = 'php7.0-mysql';
        // Prepare db mysql://drupal:drupal@localhost/drupal.
        $packages_script[] = 'mysql -u root -e "
        CREATE USER \'drupal\'@\'localhost\' IDENTIFIED BY \'drupal\';
        GRANT USAGE ON *.* TO \'drupal\'@\'localhost\' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
        CREATE DATABASE IF NOT EXISTS \`drupal\`;
        GRANT ALL PRIVILEGES ON \`drupal\`.* TO \'drupal\'@\'localhost\'"';
        // Gracefully shut down database before snapshotting.
        $packages_script[] = 'service mysql stop';
        break;

      case 'sqllite':
        $packages[] = 'php7.0-sqlite3';
        break;

      case 'postgresql':
        $packages[] = 'postgresql';
        $packages[] = 'php7.0-pgsql';
        // Prepare db pgsql://drupal:drupal@localhost/drupal.
        $packages_script[] = 'sudo -u postgres createuser --no-replication drupal';
        $packages_script[] = 'sudo -u postgres createdb --owner=drupal drupal';
        $packages_script[] = 'sudo -u postgres psql -c "ALTER USER drupal WITH PASSWORD \'drupal\';"';
        // Gracefully shut down database before snapshotting.
        $packages_script[] = 'service postgresql stop';
        break;
    }

    // Webserver.
    switch ($this->buildEntity->webspace_webserver->value) {
      case 'nginx':
        $packages[] = "nginx";
        switch ($this->buildEntity->webspace_interpreter->value) {
          case 'php7-fpm':
            $packages_script[] = 'echo "
            server {
              listen 80;
              server_name *.ply.st;
              root /var/www/html;
              index index.php;
              location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
              }
            }" > /etc/nginx/sites-enabled/default';
            break;

          case 'mod-php7':
            throw new \Exception('Cannot use mod-php7 with nginx');

          case 'php7-cgi':
            // @todo
            $packages_script[] = 'echo "
            server {
              listen 80;
              server_name *.ply.st;
              root /var/www/html;
              index index.php;
              location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass 127.0.0.1:9000;
              }
            }" > /etc/nginx/sites-enabled/default';
            break;
        }
        $packages_script[] = 'service nginx reload';
        break;

      case 'apache2':
        $packages[] = "apache2";
        // Modules and config for clean url.
        $packages_script[] = 'a2enmod rewrite';
        $packages_script[] = 'echo "
        <Directory /var/www/html>
          Options FollowSymLinks
          AllowOverride All
        </Directory>" >> /etc/apache2/sites-enabled/000-default.conf';
        $packages_script[] = 'service apache2 restart';

        switch ($this->buildEntity->webspace_interpreter->value) {
          case 'php7-fpm':
            // @todo
            break;

          case 'mod-php7':
            $packages[] = 'libapache2-mod-php7.0';
            $packages_script[] = 'echo "
            [PHP]
            max_execution_time = 300" >> /etc/php/7.0/apache2/php.ini';
            break;

          case 'php7-cgi':
            // @todo
            break;
        }
        $packages_script[] = 'rm /var/www/html/index.html';
        break;
    }

    $script[] = 'apt-get install -y ' . implode(' ', $packages);
    $script = array_merge($script, $packages_script);

    // Install composer.
    $script[] = 'php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"';
    $script[] = 'php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer';
    $script[] = 'rm composer-setup.php';

    // Install drush.
    $script[] = 'php -r "readfile(\'https://s3.amazonaws.com/files.drush.org/drush.phar\');" > drush';
    $script[] = 'chmod +x drush';
    $script[] = 'mv drush /usr/local/bin';
    return implode("\n", $script);
  }

  /**
   * Build the Drupal part of the deploy script.
   *
   * @return bool|array
   *   Deploy script for building submission instance.
   */
  protected function buildDrupal() {
    // Snapshot; Start fetching drupal projects.
    // @todo need snapshot cache invalidation to keep packages up to date
    $script = [];
    $script[] = '#!/bin/bash';
    $script[] = 'export DEBIAN_FRONTEND=noninteractive';
    $script[] = 'set -x';
    $script[] = 'echo "Starting next script - Build Drupal"';
    // @todo actually download more than drupal core
    // @todo do this with non root user
    if ($this->buildEntity->drupal_projects[0]->getFieldCollectionItem()->project_identifier->value === 'social') {
      $script[] = 'composer create-project goalgorilla/social_template:dev-master /var/www/drupal --no-interaction';
      $script[] = 'ln -s /var/www/drupal/html /var/www/drupal/web';
      $script[] = 'mkdir /var/www/drupal/html/sites/default/files';
      $script[] = 'chmod 777 /var/www/drupal/html/sites/default/files';
      $script[] = 'cp /var/www/drupal/html/sites/default/default.settings.php /var/www/drupal/html/sites/default/settings.php';
      $script[] = 'chmod 777 /var/www/drupal/html/sites/default/settings.php';
      $script[] = 'echo "
      \$settings[\'hash_salt\'] = \'notasecret\';
      \$databases[\'default\'][\'default\'] = array (
        \'database\' => \'drupal\',
        \'username\' => \'drupal\',
        \'password\' => \'drupal\',
        \'prefix\' => \'\',
        \'host\' => \'localhost\',
        \'port\' => \'\',
        \'namespace\' => \'Drupal\\Core\\Database\\Driver\\mysql\',
        \'driver\' => \'mysql\',
      );" >> /var/www/drupal/html/sites/default/settings.php';
    }
    else {
      $script[] = 'composer create-project drupal-composer/drupal-project:8.x-dev /var/www/drupal --stability dev --no-interaction';
    }
    $script[] = 'rm -rf /var/www/html';
    $script[] = 'ln -s /var/www/drupal/web /var/www/html';

    return implode("\n", $script);
  }

  /**
   * Builds the project part of the deploy script.
   *
   * @return bool|array
   *   Deploy script for building submission instance.
   */
  protected function buildProject() {
    // Snapshot; Start installing drupal projects.
    // @todo if project is dev version we shouldn't snapshot cache (for too long).
    $script = [];
    $script[] = '#!/bin/bash';
    $script[] = 'export DEBIAN_FRONTEND=noninteractive';
    $script[] = 'set -x';
    $script[] = 'echo "Starting next script - Build Project"';

    // Start up database.
    switch ($this->buildEntity->webspace_dbs->value) {
      case 'mariadb':
      case 'mysql':
        $script[] = 'service mysql start';
        break;

      case 'postgresql':
        $script[] = 'service postgresql start';
        break;
    }

    // Use webroot as cwd.
    $script[] = 'cd /var/www/drupal/web';
    // Whether we install depends of the form selection.
    // @todo if skipping drupal core installation, prefill database credentials and etc
    // can we use $settings in settings.php for that?
    // @todo install with dynamic site name? one of the project names?
    // @todo run drush with non root user
    if ($this->buildEntity->drupal_projects[0]->getFieldCollectionItem()->project_install->value) {
      switch ($this->buildEntity->webspace_dbs->value) {
        case 'mariadb':
        case 'mysql':
          $script[] = 'drush si -y --db-url=mysql://drupal:drupal@localhost/drupal --account-pass=admin --site-name=drupal';
          // Gracefully shut down database before snapshotting.
          $script[] = 'service mysql stop';
          break;

        case 'postgresql':
          $script[] = 'drush si -y --db-url=pgsql://drupal:drupal@localhost/drupal --account-pass=admin --site-name=drupal';
          // Gracefully shut down database before snapshotting.
          $script[] = 'service postgresql stop';
          break;

        case 'sqllite':
          $script[] = 'drush si -y --db-url=sqlite://sites/default/files/.ht.sqlite --account-pass=admin --site-name=drupal';
          $script[] = 'chmod 666 sites/default/files/.ht.sqlite';
          break;
      }
    }

    return implode("\n", $script);
  }

  /**
   * Builds the final tasks part of the deploy script.
   *
   * @return bool|array
   *   Deploy script for building submission instance.
   */
  protected function buildTasks() {
    $script = [];
    $script[] = '#!/bin/bash';
    // Snapshot; Snapshot installed state.
    $script[] = 'export DEBIAN_FRONTEND=noninteractive';
    $script[] = 'set -x';
    $script[] = 'echo "Starting next script - Build Tasks"';

    // Start up database.
    switch ($this->buildEntity->webspace_dbs->value) {
      case 'mariadb':
      case 'mysql':
        $script[] = 'service mysql start';
        break;

      case 'postgresql':
        $script[] = 'service postgresql start';
        break;
    }

    return implode("\n", $script);
  }

  /**
   * Makes a request to service server to destroy a build instance.
   *
   * @param SubmissionInterface $simplytest_submission
   *   Submission whose instance should be destroyed.
   *
   * @return string
   *   Response or error message to display
   */
  public function deleteBuild(SubmissionInterface $simplytest_submission) {
    $url = SubmissionInterface::SERVICE_URL;
    $url .= '/' . $simplytest_submission->container_id->value;
    $url .= '?token=' . $simplytest_submission->container_token->value;
    $client = \Drupal::httpClient();

    try {
      $request = $client->delete($url);
      $response = $request->getBody();
      $contents = $response->getContents();
    }
    catch (RequestException $e) {
      watchdog_exception('simplytest_submission', $e);
      return $e->getMessage();
    }

    return $contents;
  }

  /**
   * Output some debug information.
   *
   * @param mixed $input
   *   The variable to dump.
   * @param string $name
   *   (optional) The label to output before variable, defaults to NULL.
   */
  protected function debug($input, $name = NULL) {
    if ($this->dumper) {
      $this->dumper->message($input, $name);
    }
  }

}
