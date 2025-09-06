#!/bin/bash
# Can be called like this ./private/docker-compose.bash -d 'up -d --force-recreate' -h 'pi'

HOST_PI="192.168.0.38";
HOST_DEMO='private-host-hetzner';

COMPOSE_FILE='';
ARCHITECTURE='';
HOST='';
DIR='';

while getopts ":h:d:" opt; do
  case $opt in
    h) TARGET_HOST="$OPTARG"
      if [ "$TARGET_HOST" == "pi" ]; then
        DIR="/home/volmarg/Partitions/Apps/pms/personal-management-system";
        HOST="$HOST_PI";
        ARCHITECTURE="linux/arm/v7"
        COMPOSE_FILE='docker-compose-pi.yml'
      fi;

      if [ "$TARGET_HOST" == "demo" ]; then
        DIR="/var/www/pms/personal-management-system";
        HOST="$HOST_DEMO";
        ARCHITECTURE="linux/amd64"
        COMPOSE_FILE='docker-compose.yml'
      fi;
    ;;
    d) DOCKER_ARGS="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
    exit 1
    ;;
  esac

done

if [[ -z "$HOST" || -z "$ARCHITECTURE" || -z "$DIR"  || -z "$COMPOSE_FILE" ]]; then
  printf "Invalid target host, got '$TARGET_HOST'. Did You provided host name at all like this: -h pi?\n"
  exit 1;
fi;

if [[ -z "$DOCKER_ARGS" ]]; then
  printf "Docker args are missing, add script option -d such as: -d 'up -d --force-recreate --build'\n"
  exit 1;
fi;

ssh "$HOST" "cd $DIR && DOCKER_DEFAULT_PLATFORM=$ARCHITECTURE docker compose -f $COMPOSE_FILE $DOCKER_ARGS"
