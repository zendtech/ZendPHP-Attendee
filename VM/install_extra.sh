#!/usr/bin/sh
. ../Docker/secrets.sh
echo "Installing misc tools ..." && \
apt-get update -y
apt-get install -y podman podman-compose
ln -s /usr/bin/podman /usr/bin/docker
ln -s /usr/bin/podman-compose /usr/bin/docker-compose
echo "Assigning permissions ..."
chmod 755 $HOME_DIR
chown -R vagrant:www-data $HOME_DIR/*
find $HOME_DIR/* -type f -exec chmod 664 {} \;
find $HOME_DIR/* -type d -exec chmod 775 {} \;
chown -R www-data:www-data /var/www
echo "Course-specific VM setup complete ... "
