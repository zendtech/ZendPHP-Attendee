ARG OS=alpine
ARG OS_VERSION=3.18
ARG PHP_VER=8.2
FROM cr.zend.com/zendhq:${OS}-${OS_VERSION}
COPY license /tmp/license
COPY license /entrypoint.d/license
COPY default_monitor_rules.json /entrypoint.d/default_monitor_rules.json
COPY secrets.sh /tmp/secrets.sh
COPY nginx.default.conf /tmp/default.conf
COPY startup.sh /tmp/startup.sh
COPY copy_license.sh /tmp/copy_license.sh
RUN chmod +x /tmp/*.sh
RUN \
    echo "Installing basic utils ..." && \
    apk add bash && \
    apk add wget && \
    apk add curl && \
    apk add nano && \
    apk add sqlite
RUN \
    echo "Installing nginx ..." && \
    apk update && \
    apk add nginx && \
    cp /tmp/default.conf /etc/nginx/http.d/default.conf
RUN \
    echo "Installing PHP ..." && \
    . /tmp/secrets.sh && \
    zendphpctl php install $PHP_VER && \
    zendphpctl ext install --php $PHP_VER pdo_sqlite && \
    zendphpctl ext install --php $PHP_VER curl
RUN \
    echo "Installing PHP-FPM ..." && \
    . /tmp/secrets.sh && \
    zendphpctl fpm install $PHP_VER
RUN \
    echo "Configuring ZendPHP, ZendHQ and PHP-FPM ..." && \
    . /tmp/secrets.sh && \
    zendphpctl ext install --php $PHP_VER zendhq && \
    sed -i 's/\;zendhqd\.websocket\.interface\ \=\ \*\:10091/zendhqd\.websocket\.interface=\*\:10091/g' /opt/zend/zendphp/etc/zendhqd.ini && \
    sed -i 's/zendhqd\.websocket\.interface\ \=\ \:10091/\;zendhqd\.websocket\.interface\=\:10091/g' /opt/zend/zendphp/etc/zendhqd.ini && \
    sed -i 's/zendhqd\.daemon_uri\ \=\ tcp\:\/\/0\.0\.0\.0\:10090/;zendhqd\.daemon_uri\ \=\ tcp\:\/\/0\.0\.0\.0\:10090/g' /opt/zend/zendphp/etc/zendhqd.ini && \
    sed -i 's/zendhqd\.daemon_pub_uri\ \=\ tcp\:\/\/0\.0\.0\.0\:10092/;zendhqd\.daemon_pub_uri\ \=\ tcp\:\/\/0\.0\.0\.0\:10092/g' /opt/zend/zendphp/etc/zendhqd.ini && \
    sed -i 's/user\ \=\ nobody/user=zendphp/g'  /etc/php/82zend/php-fpm.d/www.conf && \
    sed -i 's/group\ \=\ nobody/group=zendphp/g'  /etc/php/82zend/php-fpm.d/www.conf
ENTRYPOINT /tmp/startup.sh
