#!/bin/bash
CONTAINER_NAME='pms-php-fpm'
APP_DIR="application"
NOW=$(date +"%T");

execInContainer() {
   docker exec "$CONTAINER_NAME" sh -c "$1"
}

now=$(date +"%T");
echo $'\n';
echo "Current time : $NOW";
echo '[start]';

# Clear upload folder and add files back
## images
### Clear images dir
execInContainer "rm -rf /$APP_DIR/public/upload/images/*";
echo 'Removing old images';

### Copy demo data images for upload module (images)
execInContainer "cp -r /$APP_DIR/assets/static/demoData/modules/Images/* /$APP_DIR/public/upload/images";
echo 'Copy images for MyImages';

## files
### Clear files dir
execInContainer "rm -rf /$APP_DIR/public/upload/files/*";
echo 'Remove files from myfiles';

### Copy back files for demo data
execInContainer "cp -r /$APP_DIR/assets/static/demoData/modules/Files/* /$APP_DIR/public/upload/files";
echo 'Copy data for MyFiles';

## videos
### clear videos dir
execInContainer "rm -rf /$APP_DIR/public/upload/videos/*";
echo 'Remove videos from myfiles';

### Copy back video  for demo data
execInContainer "cp -r /$APP_DIR/assets/static/demoData/modules/Video/* /$APP_DIR/public/upload/videos/";
echo 'Copy data for MyVideo';

echo 'Setting permissions';
execInContainer "chmod 774 /$APP_DIR/public/upload -R";
execInContainer "chown www-data:www-data /$APP_DIR/public/upload -R";

echo '[end]';
