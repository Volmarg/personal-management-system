#!/bin/bash
CONTAINER_NAME='pms-php-fpm'

HOST_PI="192.168.0.38";
HOST_DEMO='private-host';

USED_HOST='';
DIR='';

if [ "$1" == "pi" ]; then
  DIR="/home/volmarg/Partitions/Apps/pms/personal-management-system";
  USED_HOST="$HOST_PI";
fi;

if [ "$1" == "demo" ]; then
  DIR="/var/www/pms/personal-management-system";
  USED_HOST="$HOST_DEMO";
fi;

if [[ -z "$USED_HOST" || -z "$DIR" ]]; then
  printf "Invalid target host, got '$1'"
  exit 1;
fi;

rsync -h -v -r -P -t \
--exclude .git \
--exclude .idea \
--exclude config/packages/config/encryption.yaml \
--exclude .env \
--exclude var \
--exclude public/upload \
--stats \
--delete \
./ "$USED_HOST:$DIR"

ssh "$USED_HOST" "sudo chgrp www-data $DIR -R"
ssh "$USED_HOST" "sudo chmod 775 $DIR -R"

ssh "$USED_HOST" "sudo chmod 775 $DIR -R"

ssh "$USED_HOST" "docker exec $CONTAINER_NAME composer dump-autoload"
ssh "$USED_HOST" "docker exec $CONTAINER_NAME bin/console cache:clear"
ssh "$USED_HOST" "docker exec $CONTAINER_NAME bin/console cache:warmup"

ssh "$USED_HOST" "docker exec $CONTAINER_NAME cachetool.phar opcache:reset"
ssh "$USED_HOST" "docker exec $CONTAINER_NAME cachetool.phar apcu:cache:clear"