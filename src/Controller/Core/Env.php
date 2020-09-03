<?php

namespace App\Controller\Core;

use App\DTO\DatabaseCredentialsDTO;
use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Env extends AbstractController {

    const APP_ENV_TEST = "test";

    public static function getUploadDirs() {
        $dirs = [
          self::getImagesUploadDir(),
          self::getFilesUploadDir(),
        ];

        return $dirs;
    }

    public static function getUploadDir() {
        return $_ENV['UPLOAD_DIR'];
    }

    public static function getImagesUploadDir() {
        return $_ENV['IMAGES_UPLOAD_DIR'];
    }

    public static function getMiniaturesUploadDir() {
        return $_ENV['MINIATURES_UPLOAD_DIR'];
    }

    public static function getPublicRootDir(){
        return $_ENV['PUBLIC_ROOT_DIR'];
    }

    public static function getFilesUploadDir() {
        return $_ENV['FILES_UPLOAD_DIR'];
    }

    public static function getDatabaseUrl() {
        return $_ENV['DATABASE_URL'];
    }

    /**
     * @return DatabaseCredentialsDTO
     * @throws Exception
     */
    public static function getDatabaseCredentials(){
        $regex        = '/^mysql:\/\/(.*):(.*)@(.*):(.*)\/(.*)/';
        $database_url = self::getDatabaseUrl();

        preg_match($regex, $database_url, $matches);

        try{

            $login          = $matches[1];
            $password       = $matches[2];
            $host           = $matches[3];
            $port           = $matches[4];
            $database_name  = $matches[5];

        }catch(\Exception $e){
            throw new Exception("There was ane error while parsing database connection from .env.");
        }

        $dto = new DatabaseCredentialsDTO();
        $dto->setDatabaseLogin($login);
        $dto->setDatabaseHost($host);
        $dto->setDatabasePassword($password);
        $dto->setDatabasePort($port);
        $dto->setDatabaseName($database_name);

        return $dto;
    }

    /**
     * @return bool
     */
    public static function isDemo() {
        try {
            $is_demo = (bool)$_ENV['APP_DEMO'];
        } catch (\Exception $e) {
            $is_demo = false;
        }
        return $is_demo;
    }

    public static function isTest(): bool {
        try {
            $is_test = $_ENV['APP_ENV'] === self::APP_ENV_TEST;
        } catch (\Exception $e) {
            $is_test = false;
        }
        return $is_test;
    }

    /**
     * @return bool
     */
    public static function isMaintenance(): bool {
        try {
            $is_maintenance = (bool)$_ENV['APP_MAINTENANCE'];
        } catch (\Exception $e) {
            $is_maintenance = false;
        }
        return $is_maintenance;
    }


}
