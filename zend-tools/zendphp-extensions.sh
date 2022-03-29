#!/bin/bash

function usage(){
    cat <<EOU
Manage the extensions provided with ZendPHP.

Install extension(s) - examples:
   # $(basename "${0}") install oci8
   # $(basename "${0}") install oci8 pgsql soap

Install all extensions provided by the Zend repository (not development or debug)
   # $(basename "${0}") install all

Uninstall extension - not implemented (no use case)

Enable extension(s) - examples:
   # $(basename "${0}") enable oci8
   # $(basename "${0}") enable oci8 pgsql soap

Disable extension(s) - examples:
   # $(basename "${0}") disable oci8
   # $(basename "${0}") disable oci8 pgsql soap

List extensions - examples:
   # $(basename "${0}") list installed
   # $(basename "${0}") list installable
   # $(basename "${0}") list enabled
   # $(basename "${0}") list disabled

NB: Things are a little weird with using '-' in some places
and '_' in others. I'll be trying to properly swap them - whatever
makes more sense for a specific action.
However, things happen, just be aware of this.

EOU
}

# shellcheck source=/dev/null
. /usr/local/bin/zendphp.rc

function zenable(){
    if isCentos; then
        cd "${PHP_D_PATH}/DISABLED" > /dev/null 2>&1 || panic 1 "There seems to be nothing disabled, i.e. nothing to re-enable here."

        for xt in "$@"; do
            iniFile=$(basename "$(find . -type f -name "*-${xt//-/_}.ini")" 2> /dev/null)
            if [ -n "${iniFile}" ]; then
                mv "${iniFile}" ../ && echo "[OK] - '${xt}' should be enabled now."
            else
                echo "Can't find '${xt}' in DISABLED. Maybe already enabled?"
            fi
        done
    else
        # not making a distinction for cli and fpm - no use case
        phpenmod -v "${PHP_VER}-zend" "${@//-/_}"
    fi
}

function zdisable(){
    if isCentos; then
        cd "${PHP_D_PATH}" > /dev/null 2>&1 || panic 1 "Can't jump to the scan directory"
        mkdir -p DISABLED
        for xt in "$@"; do
            iniFile=$(basename "$(find . -maxdepth 1 -type f -name "*-${xt//-/_}.ini")" 2> /dev/null)
            if [ -n "${iniFile}" ]; then
                mv "${iniFile}" DISABLED/ && echo "[OK] - '${xt}' should be disabled now."
            else
                echo "INI for '${xt}' - not found. Maybe already disabled?"
            fi
        done
    else
        # not making a distinction for cli and fpm - no use case
        phpdismod -v "${PHP_VER}-zend" "${@//-/_}"
    fi
}

function zinstall(){
    local list=""
    local packagelist=()
    local args=( "$@" )
    echo
    if [ "X$(echo "${args[0]}" | xargs)X" == "XallX" ] || [ "X$(echo "${args[0]}" | xargs)X" == "XALLX" ]; then
        echo "Will try to install all extensions from the Zend repository..."
        while IFS= read -r xt
        do
            list="${list} ${xt}"
        done < <(grep -v '^ *#' < /etc/zendphp/installable_extensions)
    else
        for xt in ${args[*]}; do
            list="${list} $(grep -E "^php.*-?zend-(php-|php-pecl-)?${xt//_/-}\$" /etc/zendphp/installable_extensions)"
        done
        if [ "X$(echo "${list}" | xargs)X" == "XX" ]; then
            panic 1 "Extension(s) not installable:\n" "$@" "\n"
        fi
    fi
    echo -e "Will try to install:\n$list\n"
    IFS=' ' read -ra packagelist <<< "${list}"
    echo
    if isCentos; then
        yum -y install "${packagelist[@]}"
    else
        apt-get update
        apt-get install -y "${packagelist[@]}"
    fi
}

function zlist(){
    case $1 in
        installed)
            if isCentos; then
                rpm -qa "php${PHP_V}zend-php-*" --qf '%{NAME}\n' | grep -v 'php-pecl-' | grep -v "debuginfo" | grep -vE "^php${PHP_V}zend-php-(devel|embedded|fpm|cgi|cli|common)\$" | sed "s|^php${PHP_V}zend-php-||g" | sort
            else
                dpkg-query -f '${Package}\n' --show "php$PHP_VER-zend-*" | grep -vE "^php$PHP_VER-zend-(dev|fpm|cgi|cli|common)\$" | sed "s|^php${PHP_VER}-zend-||g" | sort
            fi
            ;;
        installable)
            if isCentos; then
                sed "s|^php${PHP_V}zend-php-||g" "${PHP_ETC_PATH}/installable_extensions" | sort
            else
                sed "s|^php${PHP_VER}-zend-||g" "${PHP_ETC_PATH}/mods-installable" | sort
            fi
            ;;
        enabled)
            if isCentos; then
                if [ ! -d "${PHP_D_PATH}" ];then
                    panic 1 "Couldn't change to the scan directory. I've a feeling we're not in Kansas anymore..."
                fi
                find "${PHP_D_PATH}" -name '*.ini' | cut -d'-' -f2 | cut -d'.' -f1 | sort
            else
                phpquery -d -v "${PHP_VER}-zend" -s fpm -M | grep Enabled | cut -d' ' -f1 | sort
            fi
            ;;
        disabled)
            if isCentos; then
                if [ ! -d "${PHP_D_PATH}/DISABLED" ];then
                    panic 5 "Nothing disabled yet. Not by me anyway..."
                fi
                xd=$(find "${PHP_D_PATH}/DISABLED" -name '*.ini')
                find "${PHP_D_PATH}/DISABLED" 2> /dev/null || panic 5 "Nothing disabled yet. Not by me anyway..."
                if [[ "${xd}" == "" ]];then
                    panic 3 "No disabled extensions found. Are you sure something's missing?"
                fi
                echo "${xd}" | cut -d'-' -f2 | cut -d'.' -f1 | sort
            else
                phpquery -d -v "${PHP_VER}-zend" -s fpm -M | grep Disabled | sed -r 's|^No module matches ([0-9A-Za-z_]+).*$|\1|g' | sort
            fi
            ;;
        *) usage;;
    esac
}

case "$1" in
    install|enable|disable)
        action=$1
        shift
        [[ ${#@} -gt 0 ]] || panic 1 "\nList of extensions to $action is empty\n"
        "z${action}" "$@"
        ;;
    list)
        shift
        zlist "$1"
        ;;
    *) usage;;
esac
