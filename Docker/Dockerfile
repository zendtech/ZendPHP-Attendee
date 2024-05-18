ARG OS=ubuntu
ARG OS_VERSION=22.04
ARG ZENDPHP_VERSION=8.2
ARG BASE_IMAGE=fpm
# Append -arm after $BASE_IMAGE if you wish to use the ARM variant
#FROM cr.zend.com/zendphp/${ZENDPHP_VERSION}:${OS}-${OS_VERSION}-${BASE_IMAGE}-arm
FROM cr.zend.com/zendphp/${ZENDPHP_VERSION}:${OS}-${OS_VERSION}-${BASE_IMAGE}
MAINTAINER doug@unlikelysource.com
COPY index.html /tmp/index.html
COPY startup.sh /tmp/startup.sh
COPY secrets.sh /tmp/secrets.sh
COPY backup.sh /tmp/backup.sh
COPY restore.sh /tmp/restore.sh
COPY nginx.default.conf /tmp/default.conf
COPY sandbox.conf /tmp/sandbox.conf
COPY orderapp.conf /tmp/orderapp.conf
COPY php-examples.conf /tmp/php-examples.conf
COPY dbadmin.conf /tmp/dbadmin.conf
RUN chmod +x /tmp/*.sh
RUN \
    echo "Installing misc tools ..." && \
    apt-get update && \
    apt-get install -y less vim net-tools wget unzip curl
RUN \
    echo "Installing Mariadb (MySQL open source equivalent) ..." && \
    apt-get install -y mysql-server && \
    /etc/init.d/mysql start && \
    sleep 3 && \
    . /tmp/secrets.sh && \
	mysql -uroot -ppassword -v -e "CREATE DATABASE IF NOT EXISTS $DB_NAM;" && \
	mysql -uroot -ppassword -v -e "CREATE USER IF NOT EXISTS '$DB_USR'@'$DB_HOST' IDENTIFIED BY '$DB_PWD';" && \
	mysql -uroot -ppassword -v -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USR'@'$DB_HOST';" && \
	mysql -uroot -ppassword -v -e "FLUSH PRIVILEGES;"
RUN \
    echo "Installing nginx ..." && \
    apt-get install -y nginx && \
    cp -f /tmp/index.html /var/www/html/index.html && \
    cp -f /tmp/*.conf /etc/nginx/sites-available/ && \
    ln -s /etc/nginx/sites-available/orderapp.conf /etc/nginx/sites-enabled/orderapp.conf && \
    ln -s /etc/nginx/sites-available/sandbox.conf /etc/nginx/sites-enabled/sandbox.conf && \
    ln -s /etc/nginx/sites-available/php-examples.conf /etc/nginx/sites-enabled/php-examples.conf && \
    ln -s /etc/nginx/sites-available/dbadmin.conf /etc/nginx/sites-enabled/dbadmin.conf
RUN \
    echo "Installing PHP extensions ..." && \
    zendphpctl ext install simplexml && \
    zendphpctl ext install mysqli && \
    zendphpctl ext install pdo_mysql && \
    zendphpctl ext install mbstring && \
    zendphpctl ext install openssl && \
    zendphpctl ext install gd && \
    zendphpctl ext install curl
RUN
    echo "Installing Adminer (database admin) ..." && \
    cd \tmp && \
    curl -L https://github.com/vrana/adminer/releases/download/v$DB_ADMIN_VER/adminer-$DB_ADMIN_VER.php -o adminer.php && \
    mv /tmp/adminer.php /var/www/html/adminer.php
RUN \
    . /tmp/secrets.sh && \
    echo "You need to add this to the 'etc/hosts' file on your own computer:" && \
    echo "$CONTAINER_IP   $HOST_NAME $HOST_OTHER"
ENTRYPOINT /tmp/startup.sh
