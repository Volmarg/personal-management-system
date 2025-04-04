#!/bin/bash
# Can be called like this ./private/docker-compose.bash up -d --force-recreate
# All the args added after file name are getting appended to the "docker compose" call

ARCHITECTURE="linux/arm/v7"
DIR="/home/volmarg/Partitions/Apps/pms/personal-management-system"
IP="192.168.0.38"

ssh "$IP" "cd $DIR && DOCKER_DEFAULT_PLATFORM=$ARCHITECTURE docker compose -f docker-compose-pi.yml $*"
