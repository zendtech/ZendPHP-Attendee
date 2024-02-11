#!/bin/bash

function usage(){
    ext_help="$(zendphp-extensions.sh help | sed -e "s|zendphp-extensions.sh|$(basename "${0}") EXT|g" -e 's/^/|   /g')"
    pecl_help="$(zendphp-pecl-tool.sh help | sed -e "s|zendphp-pecl-tool.sh|$(basename "${0}") PECL|g" -e 's/^/|   /g')"
    cat <<EOU

This script helps with the most common ZendPHP management tasks.

Usage: $(basename "${0}") <subcommand> <parameters>

Subcommands:
  - EXT | ext | extensions
|-----------------------------------------------------------------
${ext_help}
|-----------------------------------------------------------------

  - PECL | pecl
|-----------------------------------------------------------------
${pecl_help}
|-----------------------------------------------------------------

  - COMPOSER | getcomposer | installcomposer
|-----------------------------------------------------------------
|   Install Composer into the specified directory.
|
|   Example:
|      # $(basename "${0}") getcomposer /usr/local/bin
|
|   The specified directory must exist before the installation.
|-----------------------------------------------------------------


EOU
}

# shellcheck disable=SC1091
. /usr/local/bin/zendphp.rc

function zcomposer(){
    [[ ! -d "$1" ]] && panic 1 "The specified installation directory doesn't exist:\n  '${1}' (or maybe it's not a directory)"
    curl -sS https://getcomposer.org/installer | php -- --install-dir="${1}" --filename=composer
    chmod +x "${1}/composer"
    "${1}"/composer self-update
}

case "$1" in
    SP|systempackage|package)
        shift
        [[ ${#@} -gt 1 ]] || panic 1 "System Tool: Not enough parameters, please review help."
        zendphp-system-tool.sh "$@"
        ;;
    COMPOSER|getcomposer|installcomposer)
        zcomposer "${2}"
        ;;
    EXT|ext|extensions)
        shift
        [[ ${#@} -gt 1 ]] || panic 1 "Zend Extensions Tool: Not enough parameters, please review help."
        zendphp-extensions.sh "$@"
        ;;
    PECL|pecl)
        shift
        [[ ${#@} -gt 1 ]] || panic 1 "Pecl tool: Not enough parameters, please review help."
        zendphp-pecl-tool.sh "$@"
        ;;
    S6|s6)
        shift
        [[ ${#@} -gt 1 ]] || panic 1 "S6 tool: Not enough parameters, please review help."
        zendphp-s6-tool.sh "$@"
        ;;
    *) usage;;
esac
