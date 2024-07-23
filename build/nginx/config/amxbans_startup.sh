#!/bin/bash
## File: AMXBans Startup Script - amxbans_startup.sh
## Author: Kevin Moore <admin@sbotnas.io>
## Created: 2024/05/29
## Modified: 2024/05/29
## License: MIT License

log() {
    local message=$1
    local timestamp=$(date +"%Y-%m-%d %H:%M:%S,%3N")
    echo "$timestamp INFO $message"
}

update_php_timezone() {
    local ini_file="${PHP_INI_DIR}/conf.d/custom.ini"
    local timezone="${TZ:-UTC}"

    # Set PHP date to container timezone
    printf "\n[Date]\ndate.timezone=\"$timezone\"\n" >> "$ini_file"

    log "PHP timezone set to $timezone"
}


if [ "${INSTALL^^}" = "TRUE" ]; then
	log "Installing AMXBans"

	update_php_timezone

	# If we are attempting a reinstall on an existing volume, let's copy our install files back into our webroot
	#if [ -f "/var/www/html/include/db.config.inc.php" ]; then
	#    mv "/tmp/amxbans_install/setup.php" "/var/www/html/setup.php"
	#    mv "/tmp/amxbans_install/install/" "/var/www/html/install"
	#fi

	exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
	log "Starting AMXBans"

	# Remove install files if they still exist
	if [ -f "/var/www/html/setup.php" ]; then
	    rm "/var/www/html/setup.php"
	fi
	
	if [ -d "/var/www/html/install" ]; then
	    rm -r "/var/www/html/install"
	fi

	update_php_timezone

	exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
fi
