#!/bin/bash
. /tmp/secrets.sh
export PHP_VER_ALPINE=82
echo "Started ZendPHP image successfully"

# start nginx
/usr/sbin/nginx
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi
echo "Started nginx succesfully"

# assign add zendphp user to db_users group & assign rights
chown -R $PHP_USER:$NGINX_GRP $APP_DIR
chown -R $PHP_USER:$NGINX_GRP $DB_DIR
chmod -R 775 $APP_DIR
chmod -R 775 $DB_DIR

while sleep 60; do
  ps |grep nginx |grep -v grep
  PROCESS_1_STATUS=$?
  if [ -f $PROCESS_1_STATUS ]; then
    echo "nginx has already exited."
    exit 1
  fi
done


