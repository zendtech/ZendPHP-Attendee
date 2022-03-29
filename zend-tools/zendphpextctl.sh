#!/bin/bash

function usage(){
    cat <<EOU
A script to manage ZendPHP and extensions.

Install extension(s) - examples:
   # zendphpextctl.sh install oci8
   # zendphpextctl.sh install oci8 pgsql soap

Uninstall extension - not implemented (no use case)

Enable extension(s) - examples:
   # zendphpextctl.sh enable oci8
   # zendphpextctl.sh enable oci8 pgsql soap

Disable extension(s) - examples:
   # zendphpextctl.sh disable oci8
   # zendphpextctl.sh disable oci8 pgsql soap

List extensions - examples:
   # zendphpextctl.sh list installed
   # zendphpextctl.sh list installable
   # zendphpextctl.sh list enabled
   # zendphpextctl.sh list disabled

NB: Things are a little weird with using '-' in some places and '_' in
others. I'll be trying to properly swap them - whatever makes more sense
for a specific action. However, things happen, just be aware of this.

EOU
}

function panic(){
    # Usage: panic <exit status> <message>
    echo -e "......\n${2}\n......"
    exit "${1}"
}

function os_id(){
    if [ -n "${OS_ID}" ]; then
        true
    elif [ -f /etc/os-release ]; then
        # shellcheck source=/dev/null
        . /etc/os-release
        export OS_ID="${ID}"
    else
        # gotta be CentOS 6
        export OS_ID=centos
    fi
}

function isCentos(){
    os_id
    [[ "${OS_ID}" == "centos" ]] && return 0 || return 1
}

function isDebian(){
    os_id
    [[ "${OS_ID}" == "debian" ]] && return 0 || return 1
}

function isUbuntu(){
    os_id
    [[ "${OS_ID}" == "ubuntu" ]] && return 0 || return 1
}

function isApt(){
    os_id
    [[ "${OS_ID}" != "centos" ]] && return 0 || return 1
}

function isWhat(){
    echo "Not sure which OS this is..."
    echo "If you know, help me out by setting \$OS_ID to 'centos', 'debian' or 'ubuntu'."
    echo "Bye for now."
    echo
    exit 1
}

function zenable(){
    if isCentos; then
        cd "/etc/opt/zend/php${PHP_V}zend/php.d/DISABLED" > /dev/null 2>&1 || panic 1 "There seems to be nothing disabled, i.e. nothing to re-enable here."
        for xt in "$@"; do
            iniFile=$(basename "$(find . -type f -name "*-${xt//-/_}.ini")" 2> /dev/null)
            if [ -n "${iniFile}" ]; then
                mv "${iniFile}" ../ && echo "[OK] - '${xt}' should be enabled now."
            else
                echo "Can't find '${xt}' in DISABLED. Maybe already enabled?"
            fi
        done
    elif isApt; then
        # not making a distinction for cli and fpm - no use case
        phpenmod -v "${PHP_VER}-zend" "${@//-/_}"
    else
        isWhat
    fi
}

function zdisable(){
    if isCentos; then
        cd "/etc/opt/zend/php${PHP_V}zend/php.d" > /dev/null 2>&1 || panic 1 "Can't jump to the scan directory"
        mkdir -p DISABLED
        for xt in "$@"; do
            iniFile=$(basename "$(find . -maxdepth 1 -type f -name "*-${xt//-/_}.ini")" 2> /dev/null)
            if [ -n "${iniFile}" ]; then
                mv "${iniFile}" DISABLED/ && echo "[OK] - '${xt}' should be disabled now."
            else
                echo "INI for '${xt}' - not found. Maybe already disabled?"
            fi
        done
    elif isApt; then
        # not making a distinction for cli and fpm - no use case
        phpdismod -v "${PHP_VER}-zend" "${@//-/_}"
    else
        isWhat
    fi
}

function zinstall_rpm() {
    local list=""
    local packagelist=()
    for xt in "$@"; do
        list="${list} $(grep -E "^php${PHP_V}zend-php-.*${xt//_/-}\$" "/etc/opt/zend/php${PHP_V}zend/installable_ext")"
    done
    if [ "X$(echo "${list}" | xargs)X" == "XX" ]; then
        panic 1 "Extension(s) not installable:\n   $*\n"
    fi
    echo -e "Will try to install:\n   ${list}"
    echo "If you're trying to use this in a script, consider 'export YUM_y=-y'"
    echo
    IFS=' ' read -ra packagelist <<< "${list}"
    yum -y install "${packagelist[@]}"
    yum clean all
}

function zinstall_deb() {
    local list=""
    local packagelist=()
    for xt in "$@"; do
        list="${list} $(grep -E "^php${PHP_VER}-zend-${xt//_/-}\$" "/etc/php/${PHP_VER}/mods-installable")"
    done
    if [ "X$(echo "${list}" | xargs)X" == "XX" ]; then
        panic 1 "Extension(s) not installable:\n   $*\n"
    fi
    echo -e "Will try to install:\n   ${list}"
    echo "If you're trying to use this in a script, consider 'export DEBIAN_FRONTEND=noninteractive'"
    echo
    IFS=' ' read -ra packagelist <<< "${list}"
    apt-get update
    apt-get install -y "${packagelist[@]}"
    apt-get autoclean
}

function zinstall(){
    if isCentos; then
        zinstall_rpm "$@"
    elif isApt; then
        zinstall_deb "$@"
    else
        isWhat
    fi
}

function zlist(){
    case "${1}" in
        installed)
            if isCentos; then
                rpm -qa "php${PHP_V}zend-php-*" --qf '%{NAME}\n' | grep -v 'php-pecl-' | grep -v "debuginfo" | grep -vE "^php${PHP_V}zend-php-(devel|embedded|fpm|cgi|cli|common)\$" | sed "s|^php${PHP_V}zend-php-||g" | sort
            elif isApt; then
                dpkg-query -f '${Package}\n' --show "php$PHP_VER-zend-*" | grep -vE "^php$PHP_VER-zend-(dev|fpm|cgi|cli|common)\$" | sed "s|^php${PHP_VER}-zend-||g" | sort
            else
                isWhat
            fi
            ;;
        installable)
            if isCentos; then
                sed "s|^php${PHP_V}zend-php-||g" "/etc/opt/zend/php${PHP_V}zend/installable_ext" | sort
            elif isApt; then
                sed "s|^php${PHP_VER}-zend-||g" "/etc/php/$PHP_VER-zend/mods-installable" | sort
            else
                isWhat
            fi
            ;;
        enabled)
            if isCentos; then
                if [ ! -d "/etc/opt/zend/php${PHP_V}zend/php.d" ];then
                    panic 1 "Missing enabled modules directory"
                fi
                find "/etc/opt/zend/php${PHP_V}zend/php.d" -name '*.ini' | cut -d'-' -f2 | cut -d'.' -f1 | sort
            elif isApt; then
                phpquery -d -v "${PHP_VER}-zend" -s fpm -M | grep Enabled | cut -d' ' -f1 | sort
            else
                isWhat
            fi
            ;;
        disabled)
            if isCentos; then
                if [ ! -d "/etc/opt/zend/php${PHP_V}zend/php.d" ];then
                    panic 5 "Nothing disabled yet. Not by me anyway..."
                fi
                find "/etc/opt/zend/php${PHP_V}zend/php.d/DISABLED" -name '*.ini' | cut -d'-' -f2 | cut -d'.' -f1 | sort
            elif isApt; then
                phpquery -d -v "${PHP_VER}-zend" -s fpm -M | grep Disabled | sed -r 's|^No module matches ([0-9A-Za-z_]+).*$|\1|g' | sort
            else
                isWhat
            fi
            ;;
    esac
}


PHP_VER=$(php -n -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
PHP_V=${PHP_VER/.}
case "${1}" in
    install|enable|disable)
        action=${1}
        shift
        [[ ${#@} -gt 0 ]] || panic 1 "\nList of extensions to ${action} is empty\n"
        "z${action}" "$@"
        ;;
    list)
        shift
        zlist "$1"
    ;;
    *) usage;;
esac
