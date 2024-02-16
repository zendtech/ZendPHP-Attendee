#!/bin/bash
DIR=`pwd`
export USAGE="Usage: admin.sh up|down|build|shell|ls [zendphp2|zendhq2]"
if [[ -z "$1" ]]; then
    echo $USAGE
    exit 1
fi
if [[ "$1" = "up" || "$1" = "start" ]]; then
    docker-compose up -d
elif [[ "$1" = "down" || "$1" = "stop" ]]; then
    docker-compose down
    sudo chown -R $USER:$USER *
elif [[ "$1" = "ls" ]]; then
    docker container ls
elif [[ "$1" = "build" ]]; then
    docker-compose build
elif [[ "$1" = "shell" ]]; then
    if [[ -z ${2} ]]; then
        echo "Unable to locate running container: $2"
    else
        docker exec -it $2 /bin/bash
    fi
else
    echo $USAGE
    exit 1
fi
