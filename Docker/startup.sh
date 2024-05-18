#!/bin/bash
. /tmp/secrets.sh

# Make sure /etc/hosts has right hostnames
grep /etc/hosts -e "$HOST_OTHER"
status=$?
if [ $status -ne 0 ]; then
    echo "Updating /etc/hosts ..."
    echo "Adding: $CONTAINER_IP   $HOST_NAME $HOST_OTHER"
    echo "$CONTAINER_IP   $HOST_NAME $HOST_OTHER" >> /etc/hosts
fi

echo "Assigning permissions ..."
usermod -a -G www-data zendphp
chgrp -R www-data /repo

# Start the first process
/etc/init.d/php$PHP_VER-zend-fpm start
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start php-fpm: $status"
  exit $status
fi
echo "Started php-fpm succesfully"

# Start the second process
/etc/init.d/nginx start
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi
echo "Started nginx succesfully"

# Start the third process
/etc/init.d/mysql start
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start MySQL: $status"
  exit $status
fi
echo "Started MySQL succesfully"

while sleep 60; do
  ps -ax |grep php-fpm |grep -v grep
  PROCESS_1_STATUS=$?
  ps -ax |grep nginx |grep -v grep
  PROCESS_2_STATUS=$?
  ps -ax |grep mysql |grep -v grep
  PROCESS_3_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ -f $PROCESS_1_STATUS -o -f $PROCESS_2_STATUS -o -f $PROCESS_3_STATUS ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done


