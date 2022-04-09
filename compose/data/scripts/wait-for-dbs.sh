#!/bin/sh
sh -c 'wait-for db:3306 -t 300 -- echo "MariaDb is ready!"'
