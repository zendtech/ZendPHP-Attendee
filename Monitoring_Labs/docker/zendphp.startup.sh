#!/bin/bash
echo 'Resetting permissions'
adduser --system --home /var/zendphp --shell /sbin/nologin --uid 10000 --gid zendphp zendphp
chgrp -R zendphp /var/www
chmod -R 775 /var/www

