#!/bin/bash

# This variable exists for debugging, helps when calling the script directly from folder
#ROOT_DIR_PATH="../../"
ROOT_DIR_PATH=""

ENCRYPTION_CONF_FILE_PATH="${ROOT_DIR_PATH}config/packages/config/encryption.yaml"
BIN_CONSOLE_PATH="${ROOT_DIR_PATH}bin/console"

# Use this whenever necessary, not using standard / because generated encryption key usually has this character set
SED_SEPARATOR_CHARACTER="~"

# Write debug log msg
function logDebug() {
  echo -e "[Debug] ${1} \n"
}

# Trim provided value
function trim() {
  echo $(echo "${1}" | sed 's/^ *//;s/ *$//')
}

# Read out the env value and return it
function getEnvValue() {
  # cat - get .env content
  # grep - get line with given key
  # sed - get only the content after =
  MATCH=$(cat .env | grep -iP "${1}[ ]?=[ ]?(.*)" | sed s/.*=//);
  echo $(trim "$MATCH");
}

# Create any necessary dirs
function createDirs() {
  logDebug "Creating directories"

  UPLOAD_DIR=$(getEnvValue "UPLOAD_DIR");
  IMAGES_UPLOAD_DIR=$(getEnvValue "IMAGES_UPLOAD_DIR");
  FILES_UPLOAD_DIR=$(getEnvValue "FILES_UPLOAD_DIR");
  VIDEOS_UPLOAD_DIR=$(getEnvValue "VIDEOS_UPLOAD_DIR");
  MINIATURES_UPLOAD_DIR=$(getEnvValue "MINIATURES_UPLOAD_DIR");
  PUBLIC_ROOT_DIR=$(getEnvValue "PUBLIC_ROOT_DIR");

  mkdir -p "$UPLOAD_DIR";
  mkdir -p "$IMAGES_UPLOAD_DIR";
  mkdir -p "$FILES_UPLOAD_DIR";
  mkdir -p "$VIDEOS_UPLOAD_DIR";
  mkdir -p "$MINIATURES_UPLOAD_DIR";
  mkdir -p "$PUBLIC_ROOT_DIR";

  chown www-data:www-data "$UPLOAD_DIR" -R;
  chown www-data:www-data "$IMAGES_UPLOAD_DIR" -R;
  chown www-data:www-data "$FILES_UPLOAD_DIR" -R;
  chown www-data:www-data "$VIDEOS_UPLOAD_DIR" -R;
  chown www-data:www-data "$MINIATURES_UPLOAD_DIR" -R;
  chown www-data:www-data "$PUBLIC_ROOT_DIR" -R;
}

# Generates new encryption key and returns it
function generateEncryptionKey() {
  RESULT=$($BIN_CONSOLE_PATH --env=dev encrypt:genkey 2>/dev/null | grep -i "Ok" | sed s/\\[OK]//);

  echo $(trim "${RESULT}");
}

# returns the currently set encryption key
function getEncryptionKey() {
  KEY=$(cat "${ENCRYPTION_CONF_FILE_PATH}" | grep -Po '(?<=encrypt_key:)[ ]?.*');
  echo $(trim "${KEY}");
}

# check if encryption key is set
function isEncryptionKeySet() {
  CURR_KEY=$(getEncryptionKey)
  if [ -z "${CURR_KEY}" ]; then
    echo 0;
  else
    echo 1;
  fi;
}

# Sets the encryption key if none is yet set
function setEncryptionKey() {
  if [ "$(isEncryptionKeySet)" -eq 1 ]; then
    logDebug "Encryption key is already set - skipping"
    return 0;
  fi;

  logDebug "Setting encryption key"

  KEY=$(generateEncryptionKey)
  sed -i "s${SED_SEPARATOR_CHARACTER}encrypt_key:${SED_SEPARATOR_CHARACTER}encrypt_key: '${KEY}'${SED_SEPARATOR_CHARACTER}" "${ENCRYPTION_CONF_FILE_PATH}"
}

function generateJwtKeyPair() {
  logDebug "Generating jwt key pair - only if non exists yet."
  $BIN_CONSOLE_PATH lexik:jwt:generate-keypair --skip-if-exists;
}

# Break execution if any step crashes
set -e;

# It doesn't matter if folder already exists because there might be some packages changes in some version
logDebug "Installing composer packages"
composer install --ignore-platform-reqs;

logDebug "Doing: composer dump-autoload"
composer dump-autoload --ignore-platform-reqs;

# That's a must for some composer packages
logDebug "Setting up vendor dir rights"
chown www-data:www-data vendor/* -R

# It slowly the container boot but well it's necessary not just for initial run but each time that package will be installed
logDebug "Clearing cache"
$BIN_CONSOLE_PATH cache:clear && $BIN_CONSOLE_PATH cache:warmup;

# This is needed only for initial run
logDebug "Create database"
$BIN_CONSOLE_PATH doctrine:database:create --if-not-exists

# Must be called each time, since app might get db changes
logDebug "Execute migrations"
$BIN_CONSOLE_PATH doctrine:migrations:migrate --no-interaction;

# That's a must after the cache
logDebug "Set up var dir rights"
chown www-data:www-data var/* -R

createDirs
setEncryptionKey
generateJwtKeyPair