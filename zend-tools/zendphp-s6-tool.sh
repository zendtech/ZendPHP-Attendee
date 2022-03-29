#!/bin/bash
#shellcheck disable=SC1091
. /usr/local/bin/zendphp.rc

WORKDIR='/tmp/s6zend'

function usage(){
    cat <<EOU

Automate ZendPHP interfacing with S6-Overlay.
It downloads and installs on the filesystem Zend's well maintained,
pre-packaged, S6-Overlay artifacts for a plethora of scenarios.

Some examples:
   # $(basename "${0}") run-fpm-as-zendphp-user

EOU
}

function downloadUrlToWorkdir() {
    local archname
    archname=$(basename "${1}")
    curl -L "$1" -o "${WORKDIR}/${archname}" || panic 1 "Can't download archive ${1}!"
    echo "Succesfully downloaded archive '${1}'"
    echo
    export ARCHIVENAME=$archname
}

function extractArchive()
{
    ## We can do fancy tests to understand how to extract.
    ## Now supporting only tar.gz
    cd "${WORKDIR}" > /dev/null 2>&1 || panic 5 "Can't jump to the temporary processing directory!"
    tar -xzf "${ARCHIVENAME}"
    echo "Succesfully extracted archive '${WORKDIR}/${ARCHIVENAME}'"
    echo
}

function processFixAttrsD(){
    cd "${WORKDIR}/s6" > /dev/null 2>&1 || panic 10 "Can't jump to the S6 processing directory!"
    if [ -d ./fix-attrs.d ]; then
        echo
        echo "Processing files in '${WORKDIR}/s6/fix-attrs.d'..."
        cd "${WORKDIR}/s6/fix-attrs.d" > /dev/null 2>&1 || panic 11 "Can't jump to the S6 'fix-attrs.d' directory even if exists!"
        for attrs in *; do
            if [ -f "${attrs}" ]; then
                cp "${attrs}" "/etc/fix-attrs.d/${attrs}"
                echo "Copied file '${attrs}' to '/etc/fix-attrs.d/${attrs}'."
            fi
        done
        echo "Succesfully processed files in '${WORKDIR}/s6/fix-attrs.d'."
        echo
    fi
}

function updateFixAttsAppDir(){
    local dir
    dir=${1:-'/app'}
    if [ -f /etc/fix-attrs.d/appdir ]; then
        echo
        echo "Updating file '/etc/fix-attrs.d/appdir'..."
        sed "s/%%APPDIR%%/${dir}/"
        echo "Succesfully updated file '/etc/fix-attrs.d/appdir'."
        echo
    else
        panic 12 "Can't find 'appdir' file in '/etc/fix-attrs.d'!"
    fi
}

function processContInitD(){
    cd "${WORKDIR}/s6" > /dev/null 2>&1 || panic 20 "Can't jump to the S6 temporary processing directory!"
    if [ -d ./cont-init.d ]; then
        echo
        echo "Processing files in '${WORKDIR}/s6/cont-init.d'..."
        cd "${WORKDIR}/s6/cont-init.d" > /dev/null 2>&1 || panic 21 "Can't jump to the S6 'cont-init.d' directory even if exists!"
        for init in *; do
            if [ -f "${init}" ]; then
                cp "${init}" "/etc/cont-init.d/${init}"
                echo "Copied file '${init}' to '/etc/cont-init.d/${init}'."
            fi
        done
        echo "Succesfully processed files in '${WORKDIR}/s6/cont-init.d'."
        echo
    fi
}

function processServicesD(){
    cd "${WORKDIR}/s6" > /dev/null 2>&1 || panic 30 "Can't jump to the S6 temporary processing directory!"
    if [ -d ./services.d ]; then
        echo
        echo "Processing directories in '${WORKDIR}/s6/services.d'..."
        cd "${WORKDIR}/s6/services.d" > /dev/null 2>&1 || panic 31 "Can't jump to the S6 'services.d' directory even if exists!"
        for servicedir in *; do
            if [ -d "${servicedir}" ]; then
                if [ -f "${servicedir}/run" ]; then
                    cp -R "${servicedir}" "/etc/services.d/${servicedir}"
                    echo "Copied directory '${servicedir}' to '/etc/services.d/${servicedir}'."
                fi
            fi
        done
        echo "Succesfully processed directories in '${WORKDIR}/s6/services.d'."
        echo
    fi
}

function processContFinishD(){
    cd "${WORKDIR}/s6" > /dev/null 2>&1 || panic 40 "Can't jump to the S6 temporary processing directory!"
    if [ -d ./cont-finish.d ]; then
        echo
        echo "Processing directories in '${WORKDIR}/s6/cont-finish.d'..."
        cd "${WORKDIR}/s6/cont-finish.d" > /dev/null 2>&1 || panic 41 "Can't jump to the S6 'cont-finish.d' directory even if exists!"
        for finish in *; do
            if [ -f "${finish}" ]; then
                cp "${finish}" "/etc/cont-finish.d/${finish}"
                echo "Copied file '${finish}' to '/etc/cont-finish.d/${finish}'."
            fi
        done
        echo "Succesfully processed files in '${WORKDIR}/s6/cont-finish.d'."
        echo
    fi
}

function runfpmaszendphpuser(){
    local APPDIR="${1}"
    downloadUrlToWorkdir https://cr.zend.com/assets/unpriv-fpm.tar.gz
    cd "${WORKDIR}" > /dev/null 2>&1 || panic 10 "Can't jump to the temporary processing directory!"
    extractArchive
    processFixAttrsD
    updateFixAttsAppDir "${APPDIR}"
    processContInitD
    processServicesD
    processContFinishD
    cd "${WORKDIR}" > /dev/null 2>&1 || panic 50 "Can't jump to the temporary processing directory!"
}

FUNC="${1//-}"
if fnExists "${FUNC}";then
    echo
    echo "Running '${1}' related operations..."
    echo
    mkdir -p "${WORKDIR}"
    "${FUNC}" "$@" 
    rm -rf "${WORKDIR}" 
    echo
    echo "Opreations related to '${1}' completed succesfully!"
    echo
else
    usage
fi
