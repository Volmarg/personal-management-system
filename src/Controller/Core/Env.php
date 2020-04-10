<?php

namespace App\Controller\Core;

use App\DTO\DatabaseCredentialsDTO;
use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Env extends AbstractController {


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

    public static function isDemo() {
        try {
            $is_demo = (bool)$_ENV['APP_DEMO'];
        } catch (\Exception $e) {
            $is_demo = false;
        }
        return $is_demo;
    }

    public static function isMaintenance() {
        try {
            $is_maintenance = (bool)$_ENV['APP_MAINTENANCE'];
        } catch (\Exception $e) {
            $is_maintenance = false;
        }
        return $is_maintenance;
    }


}
