#!/bin/bash
. /tmp/secrets.sh
echo "Started ZendPHP image successfully"

# assign add zendphp user to db_users group & assign rights
chown -R $PHP_USER:$NGINX_GRP $APP_DIR
chmod -R 775 $APP_DIR

# copy the license file
/tmp/copy_license.sh

# Start the first process
/usr/sbin/php-fpm
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start php-fpm: $status"
  exit $status
fi
echo "Started php-fpm succesfully"

# Start the second process
/usr/sbin/nginx
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi
echo "Started nginx succesfully"

# Start the third process
/opt/zend/zendphp/bin/zendhqd -D -c /opt/zend/zendphp/etc/zendhqd.ini
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start zendhqd: $status"
  exit $status
fi
echo "Started zendhqd succesfully"

# Loop forever and check processes every 1 minute
while sleep 60; do
  ps |grep php-fpm |grep -v grep
  PROCESS_1_STATUS=$?
  ps |grep nginx |grep -v grep
  PROCESS_2_STATUS=$?
  ps |grep zendhqd |grep -v grep
  PROCESS_3_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ -f $PROCESS_1_STATUS -o -f $PROCESS_2_STATUS -o -f $PROCESS_3_STATUS ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done


