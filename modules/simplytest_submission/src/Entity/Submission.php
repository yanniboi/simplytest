<?php

namespace Drupal\simplytest_submission\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\simplytest_submission\SubmissionInterface;

/**
 * Defines the Submission entity.
 *
 * @ingroup simplytest
 *
 * @ContentEntityType(
 *   id = "simplytest_submission",
 *   label = @Translation("Submission"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simplytest_submission\SubmissionListBuilder",
 *     "views_data" = "Drupal\simplytest_submission\Entity\SubmissionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "add" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "edit" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\simplytest_submission\SubmissionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\simplytest_submission\SubmissionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "simplytest_submission",
 *   admin_permission = "administer submission entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "canonical" = "/admin/submission/{simplytest_submission}",
 *     "add-form" = "/submission/add",
 *     "edit-form" = "/admin/submission/{simplytest_submission}/edit",
 *     "delete-form" = "/admin/submission/{simplytest_submission}/delete",
 *     "collection" = "/admin/submission",
 *   },
 *   field_ui_base_route = "entity.simplytest_submission.collection"
 * )
 */
class Submission extends ContentEntityBase implements SubmissionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Submission ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Delete server instances if submission is deleted.
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    /* @var $action \Drupal\simplytest_submission\Plugin\Action\DeleteSubmission */
    $action = \Drupal::service('plugin.manager.action')->createInstance('simplytest_submission_delete_submission_action');
    $action->executeMultiple($entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Submission entity.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Submission status'))
      ->setDescription(t('The current status of the Submission entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values_function' => 'simplytest_submission_status_options',
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('');

    $fields['container_token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container Token'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['container_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container Url'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['container_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container ID'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['instance_image'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Instance Image'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'ubuntu:16.04/amd64' => 'ubuntu:16.04/amd64',
          'ubuntu:16.04/i386' => 'ubuntu:16.04/i386',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDefaultValue('ubuntu:16.04/amd64')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['instance_runtime'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Runtime'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          '1h' => '1 hour',
          '24h' => '1 day',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDefaultValue('1h')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['instance_snapshot_cache'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Use snapshot cache'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['webspace_dbs'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Database'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mysql' => 'mysql',
          'mariadb' => 'mariadb',
          'sqllite' => 'sqllite',
          'postgresql' => 'postgresql',
        ],
      ])
      ->setDefaultValue('mysql')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'settings' => [],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['webspace_interpreter'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Interpreter'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mod-php7' => 'mod-php7',
          'php7-fpm' => 'php7-fpm',
          'php7-cgi' => 'php7-cgi',
        ],
      ])
      ->setDefaultValue('mod-php7')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['webspace_secondary_dbs'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Secondary Database'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mongodb' => 'mongodb',
          'elasticsearch' => 'elasticsearch',
          'redis' => 'redis',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['webspace_webserver'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Webserver'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'nginx' => 'nginx',
          'apache2' => 'apache2',
        ],
      ])
      ->setDefaultValue('apache2')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Get the submission status options.
   *
   * @return array
   *   An array of status labels keyed by value.
   */
  public static function getStatusOptions() {
    $statuses = &drupal_static(__METHOD__);
    if (!isset($statuses)) {
      // @todo Add statuses.
      $statuses = [];
    }
    return $statuses;
  }

}
