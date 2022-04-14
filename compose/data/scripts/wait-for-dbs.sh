#!/bin/sh
sh -c 'wait-for db:9000 -t 300 -- echo "MongoDB is ready!"'
