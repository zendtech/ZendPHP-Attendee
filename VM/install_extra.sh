#!/usr/bin/sh
. ../Docker/secrets.sh
echo "Assigning permissions ..."
chmod 755 $HOME_DIR
chown -R vagrant:www-data $HOME_DIR/*
find $HOME_DIR/* -type f -exec chmod 664 {} \;
find $HOME_DIR/* -type d -exec chmod 775 {} \;
chown -R www-data:www-data /var/www
echo "Misc cleanup ... "
apt-get -y autoremove
echo "Course-specific VM setup complete ... "
