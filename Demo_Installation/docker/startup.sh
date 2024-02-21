#!/bin/bash
. /tmp/secrets.sh

# Start the 1st process
/etc/init.d/nginx start
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi
echo "Started nginx succesfully"

# Start the 2nd process
/etc/init.d/mysql start
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start MySQL: $status"
  exit $status
fi
echo "Started MySQL succesfully"

while sleep 60; do
  ps -ax |grep nginx |grep -v grep
  #PROCESS_1_STATUS=$?
  ps -ax |grep mysql |grep -v grep
  PROCESS_2_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ -f $PROCESS_1_STATUS -o -f $PROCESS_2_STATUS ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done


