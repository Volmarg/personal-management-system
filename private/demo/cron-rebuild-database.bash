#!/bin/bash
CONTAINER_NAME='pms-php-fpm'
NOW=$(date +"%T");

execInContainer() {
   docker exec "$CONTAINER_NAME" $1
}

echo "Current time : $NOW";
echo 'Rebuilding demo database';

# Create database and run migrations
echo 'Dropping database';
execInContainer "php bin/console doctrine:database:drop --force --env=dev";

echo 'Creating database';
execInContainer "php bin/console doctrine:database:create --env=dev";

echo 'Calling migrations executions';
execInContainer "php bin/console doctrine:migrations:migrate --no-interaction --env=dev";

# Truncate tables which has duplicated data from fixtures/migrations - must be in this order
echo 'Cleaning certain tables before inserting fixtures data';

# these calls must remain like this, else there is some issue with the quotes getting removed when passed to func.
docker exec "$CONTAINER_NAME" php bin/console doctrine:query:sql 'DELETE FROM my_contact_type' --env=dev;
docker exec "$CONTAINER_NAME" php bin/console doctrine:query:sql 'DELETE FROM my_contact' --env=dev;
docker exec "$CONTAINER_NAME" php bin/console doctrine:query:sql 'DELETE FROM my_contact_group' --env=dev;
docker exec "$CONTAINER_NAME" php bin/console doctrine:query:sql 'DELETE FROM my_schedule' --env=dev;

echo 'Appending fixtures';
execInContainer "php bin/console doctrine:fixtures:load --append --no-interaction --env=dev";

echo 'Demo database has been rebuilt';
