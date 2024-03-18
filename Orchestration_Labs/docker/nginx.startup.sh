#!/bin/bash
echo 'Resetting permissions'
chown -R nginx /var/www
chmod -R 775 /var/www
echo 'Staring nginx'
/usr/sbin/nginx
STATUS=$?
if [ $STATUS -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $STATUS
fi
echo "Started nginx succesfully"
while sleep 60; do
  ps |grep nginx |grep -v grep
  PROCESS_1_STATUS=$?
  if [ -f $PROCESS_1_STATUS ]; then
    echo "nginx has already exited."
    exit 1
  fi
done


