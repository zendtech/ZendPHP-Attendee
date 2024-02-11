#!/bin/bash
export PHP_VER="8.2"
export PHP_VER_ALPINE="82"
export HOST_NAME="zendphp.local"
export HOST_URL=http://$HOST_NAME/
export DB_GRP=db_users
export DB_DIR=/var/lib/data
export DB_FN=$DB_DIR/training.db
export DB_HOST="127.0.0.1"
export ADMINER_VER="4.8.1"
export HOME_DIR=/home/training
export CONTAINER=zendphp
export CONTAINER_IP="10.10.60.10"
export CONTAINER_SUBNET="10.10.60.0\/24"
export CONTAINER_GATEWAY="10.10.60.1"
export DOCKER_DEFAULT_PLATFORM=linux/amd64
