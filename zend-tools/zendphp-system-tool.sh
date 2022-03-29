#!/bin/bash

function usage(){
    cat <<EOU
Manage the extensions provided with ZendPHP.

Install extension(s) - examples:
   # $(basename "${0}") install libsodium
   # $(basename "${0}") install librdkafka librdkafka-devel

Uninstall extension - not implemented (no use case)

EOU
}

#shellcheck disable=SC1091
. /usr/local/bin/zendphp.rc

zsetup(){
    yum -y install epel-release
    [[ "${OS_VER}" -gt 7 ]] && dnf -y install dnf-plugins-core
    [[ "${OS_VER}" -gt 7 ]] && find /etc/yum.repos.d/ -type f -iname '*PowerTools*' -exec sed -i -e 's/^enabled=0/enabled=1/' '{}' \;
}

function zinstall(){
    local list=""
    local packagelist=()
    for package in "$@"; do
        list="${list} ${package}"
    done
    echo -e "Will try to install:\n ${list}"
    IFS=' ' read -ra packagelist <<< "${list}"
    echo
    if isCentos; then
        yum -y install "${packagelist[@]}"
    else
        apt-get update
        apt-get install -y "${packagelist[@]}"
    fi
}

case "${1}" in
    setup)
        action=$1
        "z${action}"
        ;;
    install)
        action=$1
        shift
        [[ ${#@} -gt 0 ]] || panic 1 "\nList of extensions to ${action} is empty\n"
        "z${action}" "$@"
        ;;
    *) usage;;
esac
