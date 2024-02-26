#!/bin/bash
echo 'Resetting permissions'
chgrp -R zendphp /var/www
chmod -R 775 /var/www
