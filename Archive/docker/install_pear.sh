#!/bin/sh
apt-get update
apt-get install -y php8.1-zend-dev php8.1-zend-xml libssl-dev
# Fix problems in PEAR like this:
# PHP Fatal error:  Array and string offset access syntax with curly braces is no longer supported in /usr/share/php/PEAR/Config.php on line 2095
# find /usr/share/php -exec php -l {} \; |grep -v "No syntax errors"
find /usr/share/php -type f -exec sed -i 's/{0}/[0]/g' {} \;
find /usr/share/php -type f -exec sed -i 's/{1}/[1]/g' {} \;
find /usr/share/php -type f -exec sed -i 's/{strlen($value) - 1}/[strlen($value) - 1]/g' {} \;
find /usr/share/php -type f -exec sed -i 's/$arg{$i}/$arg[$i]/g' {} \;
find /usr/share/php -type f -exec sed -i 's/$spec{2}/$spec[2]/g' {} \;
find /usr/share/php -type f -exec sed -i 's/{strlen($root) - 1}/[strlen($root) - 1]/g' {} \;
sed -i 's/time\(\) - $cacheid/\!empty\($cacheid\) && time\(\) - $cacheid/g' /usr/share/php/PEAR/REST.php
