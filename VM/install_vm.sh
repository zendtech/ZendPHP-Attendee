#!/usr/bin/sh
. ../Docker/secrets.sh
cd $HOME_DIR
cd Zend
echo "Installing misc tools ..." && \
apt-get update -y
apt-get install -y git net-tools docker docker-compose geany curl unzip
echo "Installing database ..."
apt-get install -y mysql-server
/etc/init.d/mysql start
sleep 3
mysql -uroot -ppassword -v -e "CREATE DATABASE IF NOT EXISTS $DB_NAM;"
mysql -uroot -ppassword -v -e "CREATE USER IF NOT EXISTS '$DB_USR'@'$DB_HOST' IDENTIFIED BY '$DB_PWD';"
mysql -uroot -ppassword -v -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USR'@'$DB_HOST';"
mysql -uroot -ppassword -v -e "FLUSH PRIVILEGES;"
echo "Restoring database ..."
mysql -uroot -ppassword -v -e "SOURCE $DB_FN;" $DB_NAM
echo "Installing and configuring nginx ..."
apt-get install -y nginx
/etc/init.d/nginx restart
echo "Installing Adminer ..."
curl -L https://github.com/vrana/adminer/releases/download/v$DB_ADMIN_VER/adminer-$DB_ADMIN_VER.php -o adminer.php
mv ./adminer.php /var/www/html/adminer.php
echo "Installing Composer ..."
curl -L https://getcomposer.org/composer.phar -o composer.phar
chmod +x ./composer.phar
mv ./composer.phar /usr/local/bin/composer
echo "Updating /etc/hosts ..."
echo "127.0.0.1    $HOST_OTHER" >> /etc/hosts
echo "Core VM setup complete ... "
