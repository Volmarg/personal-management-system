#!/bin/bash
CONTAINER_NAME='pms-php-fpm'

DIR="/home/volmarg/Partitions/Apps/pms/personal-management-system"
IP="192.168.0.38"
rsync -h -v -r -P -t \
--exclude data \
--exclude .git \
--exclude .idea \
--exclude config/packages/config/encryption.yaml \
--exclude .env \
--exclude var \
--exclude public/upload \
--stats \
--delete \
./ "$IP:$DIR"

ssh "$IP" "sudo chgrp www-data $DIR -R"
ssh "$IP" "sudo chmod 775 $DIR -R"

ssh "$IP" "sudo chmod 775 $DIR -R"

ssh "$IP" "docker exec $CONTAINER_NAME composer dump-autoload"
ssh "$IP" "docker exec $CONTAINER_NAME bin/console cache:clear"
ssh "$IP" "docker exec $CONTAINER_NAME bin/console cache:warmup"
