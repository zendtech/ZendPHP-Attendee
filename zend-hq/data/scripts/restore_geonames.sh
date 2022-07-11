#!/bin/sh
echo "Creating geonames database and assigning permissions ..."
/etc/init.d/mariadb start
sleep 5
mysql -uroot -v -e "CREATE DATABASE geonames;"
mysql -uroot -v -e "CREATE USER 'geonames'@'localhost' IDENTIFIED BY 'password';"
mysql -uroot -v -e "GRANT ALL PRIVILEGES ON *.* TO 'geonames'@'localhost';"
mysql -uroot -v -e "FLUSH PRIVILEGES;"
mysql -uroot -v -e "SOURCE /tmp/geonames.sql;"
