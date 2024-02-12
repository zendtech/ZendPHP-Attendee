#!/bin/bash
. ./secrets.sh
DIR=`pwd`
export USAGE="Usage: admin.sh up|down|build|shell|ip|ls [--show]"
if [[ -z "$1" ]]; then
    echo $USAGE
    exit 1
fi
if [[ "$1" = "up" || "$1" = "start" ]]; then
    if [[ -f "$2" && "$2" = "--show" ]]; then
        docker-compose up
    else
        docker-compose up -d
    fi
elif [[ "$1" = "down" || "$1" = "stop" ]]; then
    docker-compose down
    sudo chown -R $USER:$USER *
elif [[ "$1" = "ls" ]]; then
    docker container ls
elif [[ "$1" = "build" ]]; then
    docker-compose build
elif [[ "$1" = "shell" ]]; then
    if [[ -z ${CONTAINER} ]]; then
        echo "Unable to locate running container: $CONTAINER"
    else
        docker exec -it $CONTAINER /bin/bash
    fi
elif [[ "$1" = "ip" ]]; then
    echo "The IP address for the Docker container is: $CONTAINER_IP"
    echo "Open your local /etc/hosts file in a text editor as the root user"
    echo "Add this line at the end of your /etc/hosts file:"
    echo "$CONTAINER_IP    $HOST_NAME sandbox orderapp php-examples dbadmin"
else
    echo $USAGE
    exit 1
fi
