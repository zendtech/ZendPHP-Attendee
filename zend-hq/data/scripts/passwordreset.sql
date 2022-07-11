CREATE USER IF NOT EXISTS geonames@'%' IDENTIFIED BY 'password';
SET PASSWORD FOR geonames@'%' = PASSWORD('password');
GRANT ALL ON geonames.* TO geonames@'%';
CREATE USER IF NOT EXISTS root@localhost IDENTIFIED BY 'password';
SET PASSWORD FOR root@localhost = PASSWORD('password');
GRANT ALL ON *.* TO root@localhost WITH GRANT OPTION;
CREATE USER IF NOT EXISTS root@'%' IDENTIFIED BY 'password';
SET PASSWORD FOR root@'%' = PASSWORD('password');
GRANT ALL ON *.* TO root@'%' WITH GRANT OPTION;
