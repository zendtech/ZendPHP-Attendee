#!/usr/bin/sh
. ../Docker/secrets.sh
echo "Installing misc tools ..."
apt-get update -y
apt-get install -y podman podman-compose sqlite3
ln -s /usr/bin/podman /usr/bin/docker
ln -s /usr/bin/podman-compose /usr/bin/docker-compose
echo "Setting up demo app ..."
cp $REPO_DIR/Zend/Course_Assets/training.db $REPO_DIR/Zend/Basic_Installation/mezzio/data/training.db
cd $REPO_DIR/Zend/Basic_Installation/mezzio/
composer install
echo "Assigning permissions ..."
chmod 755 $HOME_DIR
chown -R vagrant:www-data $HOME_DIR/*
find $HOME_DIR/* -type f -exec chmod 664 {} \;
find $HOME_DIR/* -type d -exec chmod 775 {} \;
chown -R www-data:www-data /var/www
echo "Course-specific VM setup complete ... "
