#!/bin/bash

# Make sure we run as root.
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root!"
   exit 1
fi

# Confirm.
read -p "WARNING, THIS INSTALLATION REQUIRES A CLEAN UBUNTU 12.04 SERVER SETUP.
It will setup a complete simplytest.me worker environment.

This script assumes to be executed on a just installed ubuntu 12.04 environment
and that you uploaded _only_ this script somewhere onto the server.

Are you sure that you want to proceed? [y] " -n 1
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

# Get spawner user data.
read -p "
For security reasons a new 'spawner' user must be created.
This user will be used to trigger the build of a sandbox and is the user
that should be referenced on the simplytest.me server configuration.

Username (default is 'spawner'): "
if [[ -n $REPLY ]]
then
  SPAWNER_USER=$REPLY
else
  SPAWNER_USER="spawner"
fi
read -p "
Password (default is a random string): "
if [[ -n $REPLY ]]
then
  SPAWNER_PASSWORD=$REPLY
else
  SPAWNER_PASSWORD=$(tr -dc "[:alpha:]" < /dev/urandom | head -c 10)
  echo "Used password is: $SPAWNER_PASSWORD"
fi

read -p "
Home-server URL (default is http://simplytest.me/): "
if [[ -n $REPLY ]]
then
  HOME_SERVER_URL=$REPLY
else
  HOME_SERVER_URL="http://simplytest.me/"
  echo "Used url is: $HOME_SERVER_URL"
fi
HOME_CALLBACK_URL="${HOME_SERVER_URL}simplytest/state"
echo "State callback URL is: $HOME_CALLBACK_URL"

# Get mysql root password.
read -p "
MySQL root password to set (default is a random string): "
if [[ -n $REPLY ]]
then
  MYSQL_PASSWORD=$REPLY
else
  MYSQL_PASSWORD=$(tr -dc "[:alpha:]" < /dev/urandom | head -c 10)
  echo "Used password is: $MYSQL_PASSWORD"
fi

read -p "
SUMMARY:

Spawner user
Name: $SPAWNER_USER
Password: $SPAWNER_PASSWORD

Home server URL: $HOME_SERVER_URL
State callback URL: $HOME_CALLBACK_URL

MySQL root password: $MYSQL_PASSWORD

Are you sure that you want to proceed? [y] " -n 1
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

apt-get -y update && apt-get -y upgrade

echo ">>>> Install apache2."
apt-get install -y apache2-mpm-worker
apt-get install -y apache2-suexec-custom
apt-get install -y libapache2-mod-fcgid

echo ">>>> Install php5."
apt-get install -y php5-cgi
apt-get install -y php5-suhosin
apt-get install -y php5-gd
apt-get install -y php5-curl
apt-get install -y php5-mysql

echo ">>>> Install python libraries"
apt-get install -y python-dev
apt-get install -y python-setuptools
apt-get install -y libyaml-dev
easy_install pyyaml

echo ">>>> Install MySQL."
echo "mysql-server-5.5 mysql-server/root_password password $MYSQL_PASSWORD" | debconf-set-selections
echo "mysql-server-5.5 mysql-server/root_password_again password $MYSQL_PASSWORD" | debconf-set-selections
apt-get -y install mysql-server-5.5
apt-get install -y mysql-client

echo ">>>> Install required shell tools."
apt-get install -y at
apt-get install -y inotify-tools
apt-get install -y timeout
apt-get install -y dos2unix
apt-get install -y git
apt-get install -y unzip

echo ">>>> Install drush."
apt-get install -y php-pear
pear upgrade --force Console_Getopt
pear upgrade --force pear
pear upgrade-all
pear channel-discover pear.drush.org
pear install drush/drush-6.2.0.0

echo ">>>> Setup apache."
a2enmod suexec
a2enmod rewrite
echo "<IfModule mod_fcgid.c>
  AddHandler fcgid-script .fcgi .php
  FcgidConnectTimeout 20
</IfModule>" > /etc/apache2/mods-enabled/fcgid.conf
mkdir /var/www/wrappers
echo "RedirectMatch 301 ^/.*$ http://simplytest.me/" > /var/www/.htaccess
service apache2 restart

echo ">>>> Cloning simplytest.me"
git clone --branch 7.x-1.x git://git.drupal.org/project/simplytest.git ~/simplytest

echo ">>>> Setup spawner user"
apt-get install -y whois
useradd -s /bin/bash -m $SPAWNER_USER -p $(/usr/bin/mkpasswd -Hmd5 $SPAWNER_PASSWORD)
cp ~/simplytest/scripts/spawn.sh "/home/$SPAWNER_USER"
sed -i 's/DIRECTORY=.*/DIRECTORY=\/root\/simplytest\/scripts/g' "/home/$SPAWNER_USER/spawn.sh"
echo "Cmnd_Alias SIMPLYTESTSPAWN_CMDS = /home/$SPAWNER_USER/spawn.sh
$SPAWNER_USER ALL=(ALL) NOPASSWD: SIMPLYTESTSPAWN_CMDS" > /etc/sudoers.d/spawner
chmod 0440 /etc/sudoers.d/spawner

echo ">>>> Setting configuration"
cat ~/simplytest/scripts/default-config >> ~/simplytest/scripts/config
echo "" >> ~/simplytest/scripts/config
echo "S_SQLPWD=\"$MYSQL_PASSWORD\"" >> ~/simplytest/scripts/config
echo "S_HOME=\"$HOME_SERVER_URL\"" >> ~/simplytest/scripts/config
echo "S_CALLBACK=\"$HOME_CALLBACK_URL\"" >> ~/simplytest/scripts/config
echo "S_DRUSHCACHE=\"/home/$SPAWNER_USER/.drush/cache/git\"" >> ~/simplytest/scripts/config

echo ">>>> Optimizing"
# Make drush use git with caching for downloads.
mkdir -p /etc/drush
cp ~/simplytest/scripts/drushrc.php /etc/drush/drushrc.php
# Install APC.
apt-get install -y php-apc
# Use MyISAM.
echo "[mysqld]
skip-external-locking
skip-innodb
default_storage_engine=MyISAM" > /etc/mysql/conf.d/simplytest.cnf
# MyISAM Optimization.
echo "[mysqld]
bulk_insert_buffer_size=2G
join_buffer_size=128M
key_buffer_size=128M
max_allowed_packet=32M
query_cache_limit=64M
read_buffer_size=10M
read_rnd_buffer_size=2M
sort_buffer_size=128M
table_cache=1024
tmp_table_size=128M" > /etc/mysql/conf.d/myisam.cnf
# Move tables to ramdisk.
stop mysql
mkdir /var/lib/.mysql
chown -R mysql:mysql /var/lib/.mysql
rsync -a --delete /var/lib/mysql/ /var/lib/.mysql
rm -f /etc/init/mysql.conf
cp ~/simplytest/scripts/upstart-mysql-tmpfs.conf /etc/init/mysql.conf
start mysql

echo ">>>> Initial drupal core fetch"
git clone --mirror "git://git.drupal.org/project/drupal.git" "/home/$SPAWNER_USER/.drush/cache/git/drupal.git"

chmod 777 /tmp
chmod 777 /tmp/drush

echo "

FINISHED! If there weren't any errors you should now be able
to configure the simplytest.me site to use this server.

Go to your servers configuration and set the following:
  Active            [ticked]
  Name              [Your choise]
  Hostname          [The hostname with which this server is reachable]
  SSH Port          [This servers SSH port, should be 22]
  SSH Username      $SPAWNER_USER
  SSH Password      $SPAWNER_PASSWORD
  Slots             [Your estaminated maximum count of sites this server
                    can handle simoultanously]
  Spawn script      sudo /home/$SPAWNER_USER/spawn.sh
"