#!/bin/bash

set -e

#shellcheck disable=SC1091
. /usr/local/bin/zendphp.rc

if [ -n "${TZ}" ]; then
    echo
    echo "Setting php.ini timezone to ${TZ}"
    sed -i -e "s|;date.timezone =|date.timezone = ${TZ}|" "${PHP_INI}"
fi

if [ -n "${ZENDPHP_REPO_USERNAME}" ] && [ -n "${ZENDPHP_REPO_PASSWORD}" ]; then
    echo
    echo "Setting up credentials for restricted repo."
    php /usr/local/bin/zendphp_credentials setup-credentials "${ZENDPHP_REPO_USERNAME}" "${ZENDPHP_REPO_PASSWORD}"
fi

# Install system packages, usually as dependencies of 
# Zend or PECL extensions
if [ -n "${SYSTEM_PACKAGES}" ]; then
    SPL=''
    for SP in ${SYSTEM_PACKAGES//,/ };do
        SPL="${SPL} ${SP}"
    done
    IFS=' ' read -ra systempackagelist <<< "${SPL}"
    echo
    echo "Adding system packages: ${systempackagelist[*]}"
    zendphpctl.sh systempackage install "${systempackagelist[@]}"
fi

# Install PHP extensions pre-compiled by Zend, from the following list:
#   bcmath dba dbg enchant ffi gd gmp imap intl json ldap litespeed
#   mbstring mysqlnd oci8 odbc pdo pdodblib pear apcu igbinary imagick
#   memcached mongodb msgpack oauth redis5 ssh2 xdebug pgsql process 
#   pspell snmp soap sodium tidy xml xmlrpc zip
if [ -n "$ZEND_EXTENSIONS_LIST" ]; then
    ZEL=''
    for ZEXT in ${ZEND_EXTENSIONS_LIST//,/ };do
         ZEL="${ZEL} ${ZEXT}"
    done
    IFS=' ' read -ra zendextensionslist <<< "${ZEL}"
    echo
    echo "Adding PHP extensions: ${zendextensionslist[*]}"
    zendphpctl.sh ext install "${zendextensionslist[@]}"
fi

# Install PHP extensions from the PECL repos using
# PECL command base operations
if [ -n "$PECL_EXTENSIONS_LIST" ]; then
    PEL=''
    for PEXT in ${PECL_EXTENSIONS_LIST//,/ };do
        PEL="${PEL} ${PEXT}"
    done
    IFS=' ' read -ra peclextensionslist <<< "${PEL}"
    echo
    echo "Adding PECL extensions: ${peclextensionslist[*]}"
    zendphpctl.sh pecl build "${peclextensionslist[@]}"
fi

# Install Composer in the system path
if [[ true == "${INSTALL_COMPOSER}" ]]; then
    echo
    echo "Installing Composer"
    zendphpctl.sh installcomposer /usr/local/sbin
fi

# Install development configurations
if [[ "${ZEND_PROFILE}" =~ ^dev(elopment)*$|^DEV(ELOPMENT)*$ ]]; then
    if isFpm; then
        echo
        echo "Setting up development profile..."
        if isCentos; then
            echo "Installing php-fpm configuration for RPM distros..."
            [[ ${PHP_V} -gt 72 ]] && cp /var/centos-php-fpm.gt72.dev.conf /etc/zendphp/php-fpm.conf
            [[ ${PHP_V} -lt 73 ]] && cp /var/centos-php-fpm.lt73.dev.conf /etc/zendphp/php-fpm.conf
            cp /var/centos-www.dev.conf /etc/zendphp/php-fpm.d/www.conf
        else
            echo "Installing php-fpm configuration for APT distros..."
            [[ ${PHP_V} -gt 72 ]] && cp /var/ubuntu-php-fpm.gt72.dev.conf /etc/zendphp/fpm/php-fpm.conf
            [[ ${PHP_V} -lt 73 ]] && cp /var/ubuntu-php-fpm.lt73.dev.conf /etc/zendphp/fpm/php-fpm.conf
            cp /var/ubuntu-www.dev.conf /etc/zendphp/fpm/pool.d/www.conf
        fi
        echo "Development profile setting complete."
    fi
fi

if [ -n "${POST_BUILD_BASH}" ]; then
	if [ -f "${POST_BUILD_BASH}" ]; then
        echo
        echo "Running ${POST_BUILD_BASH}"
		chmod +x "${POST_BUILD_BASH}"
		bash -c "${POST_BUILD_BASH}"
    elif [ -f "/usr/local/sbin/${POST_BUILD_BASH}" ]; then
        echo
        echo "Running /usr/local/sbin/${POST_BUILD_BASH}"
        chmod +x "/usr/local/sbin/${POST_BUILD_BASH}"
        bash -c "${POST_BUILD_BASH}"
	fi
fi

if isCentos; then
    echo
    echo "Cleaning up YUM cache"
    yum clean all
    rm -rf /tmp/* /var/centos*.conf
else
    echo
    echo "Cleaning up DEB cache"
    apt-get -y autoremove && apt-get -y clean
    rm -rf /tmp/* /var/lib/apt/lists/* /var/ubuntu*.conf
fi

if isFpm; then
    if [[ true == "${RUN_FPM_AS_ZENDPHP_USER}" ]] && [ -n "${RUN_FPM_AS_ZENDPHP_USER_APPDIR}" ]; then
        echo
        echo "Running FPM process with unprivileged user 'zendphp'"
        zendphpctl.sh s6 run-fpm-as-zendphp-user "${RUN_FPM_AS_ZENDPHP_USER_APPDIR}"
    fi
fi

exit 0
