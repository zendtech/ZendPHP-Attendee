#!/bin/bash
set -eo pipefail
shopt -s nullglob

#shellcheck disable=SC1091
. /usr/local/bin/zendphp.rc

# logging functions
_log() {
	local type="$1"; shift
	# accept argument string or stdin
	local text="$*"; if [ "$#" -eq 0 ]; then text="$(cat)"; fi
	local dt; dt="$(date --rfc-3339=seconds)"
	printf '%s [%s] [entrypoint.d]: %s\n' "${dt}" "${type}" "${text}"
}
_notice() {
	_log NOTICE "$@"
}

_warning() {
	_log WARNING "$@" >&2
}
_error() {
	_log ERROR "$@" >&2
	exit 1
}

# check to see if this file is being run or sourced from another script
_is_sourced() {
	# https://unix.stackexchange.com/a/215279
	[ "${#FUNCNAME[@]}" -ge 2 ] \
		&& [ "${FUNCNAME[0]}" = '_is_sourced' ] \
		&& [ "${FUNCNAME[1]}" = 'source' ]
}

# usage: docker_process_init_files [file [file [...]]]
#    ie: docker_process_init_files /entrypoint.d/*
# process initializer files, based on file extensions
docker_process_init_files() {
	echo
	local f
	for f; do
		case "${f}" in
			*.sh)
				if [ -x "${f}" ]; then
					_notice "${0}: running ${f}"
					"${f}"
				else
					_notice "${0}: sourcing ${f}"
                    # shellcheck disable=SC1090
					. "${f}"
				fi
				;;
			*) _warning "${0}: ignoring ${f}" ;;
		esac
		echo
	done
}

# Verify that the minimally required settings are present.
docker_verify_minimum_env() {
	true
}

# Loads various settings that are used elsewhere in the script
docker_setup_env() {
	# Get config
	declare -g RUNFILE FIRSTRUN CONTAINERTYPE
	RUNFILE='/entrypoint.d/runfile'
	if [ ! -f "${RUNFILE}" ]; then
		FIRSTRUN='true'
	else
		RUN=$(cat "${RUNFILE}")
		RUN=$((RUN + 1))
		echo "${RUN}" > "${RUNFILE}"
	fi
	if [ -x "/opt/zend/php${PHP_V}zend/root/usr/sbin/php-fpm" ] || [ -x "/usr/sbin/php-fpm${PHP_VER}-zend" ]; then
		CONTAINERTYPE='FPM'
	else
		CONTAINERTYPE='CLI'
	fi
}

_main() {
	# Load various environment variables
	docker_setup_env "$@"
    # First run, let's initialize
    if [ "$FIRSTRUN" ]; then
        _notice "entrypoint.d scripts for PHP ${PHP_VER} ${CONTAINERTYPE} started..."
        ls /entrypoint.d/ > /dev/null
        docker_process_init_files /entrypoint.d/*
        echo 1 > "${RUNFILE}"
        _notice "entrypoint.d scripts processing done."
        echo
    fi
}

# If we are sourced from elsewhere, don't perform any further actions
if ! _is_sourced; then
	_main "$@"
fi
