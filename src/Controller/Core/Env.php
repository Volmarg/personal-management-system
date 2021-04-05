<?php

namespace App\Controller\Core;

use App\Controller\Utils\Utils;
use App\DTO\DatabaseCredentialsDTO;
use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Env extends AbstractController {

    const APP_ENV_TEST                    = "test";
    const APP_DEFAULT_NPL_RECEIVER_EMAILS = "APP_DEFAULT_NPL_RECEIVER_EMAILS";
    const APP_SHOW_INFO_BLOCKS            = 'APP_SHOW_INFO_BLOCKS';

    public static function getUploadDirs() {
        $dirs = [
          self::getImagesUploadDir(),
          self::getFilesUploadDir(),
          self::getVideoUploadDir(),
        ];

        return $dirs;
    }

    public static function getUploadDir() {
        return $_ENV['UPLOAD_DIR'];
    }

    public static function getImagesUploadDir() {
        return $_ENV['IMAGES_UPLOAD_DIR'];
    }

    public static function getVideoUploadDir() {
        return $_ENV['VIDEOS_UPLOAD_DIR'];
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
     * Will return the default emails receivers for all emails sent in NPL
     *
     * @return array
     * @throws Exception
     */
    public static function getNotifierProxyLoggerDefaultReceiversEmails(): array
    {
        $emailsString = Utils::getRealArrayForStringArray($_ENV[self::APP_DEFAULT_NPL_RECEIVER_EMAILS]);
        return $emailsString;
    }

    /**
     * Returns the information whether the guide mode is on/off
     * @return bool
     */
    public static function isGuide() {
        try {
            $isGuide = Utils::getBoolRepresentationOfBoolString($_ENV['APP_GUIDE']);
        } catch (\Exception $e) {
            $isGuide = false;
        }
        return $isGuide;
    }

    /**
     * @return DatabaseCredentialsDTO
     * @throws Exception
     */
    public static function getDatabaseCredentials(){
        $regex       = '#^mysql:\/\/(.*):(.*)@([^:]*)([:])?(.*)\/(.*)#';
        $databaseUrl = self::getDatabaseUrl();

        preg_match($regex, $databaseUrl, $matches);

        try{

            $login        = $matches[1] ?? "";
            $password     = $matches[2] ?? "";
            $host         = $matches[3] ?? "";
            $port         = $matches[5] ?? "";
            $databaseName = $matches[6] ?? "";

        }catch(\Exception $e){
            throw new Exception("There was ane error while parsing database connection from .env. {$e->getMessage()}");
        }

        $dto = new DatabaseCredentialsDTO();
        $dto->setDatabaseLogin($login);
        $dto->setDatabaseHost($host);
        $dto->setDatabasePassword($password);
        $dto->setDatabasePort($port);
        $dto->setDatabaseName($databaseName);

        return $dto;
    }

    /**
     * @return bool
     */
    public static function isDemo() {
        try {
            $isDemo = Utils::getBoolRepresentationOfBoolString($_ENV['APP_DEMO']);
        } catch (\Exception $e) {
            $isDemo = false;
        }
        return $isDemo;
    }

    /**
     * @description Special check if the `test` mode for phpunit is turned on,
     *              some code elements requires special handling for such case
     *
     * @return bool
     */
    public static function isTest(): bool {
        try {
            $isTest = $_ENV['APP_ENV'] === self::APP_ENV_TEST;
        } catch (\Exception $e) {
            $isTest = false;
        }
        return $isTest;
    }

    /**
     * @return bool
     */
    public static function isMaintenance(): bool {
        try {
            $isMaintenance = Utils::getBoolRepresentationOfBoolString($_ENV['APP_MAINTENANCE']);
        } catch (\Exception $e) {
            $isMaintenance = false;
        }
        return $isMaintenance;
    }

    /**
     * @return bool
     */
    public static function areInfoBlocksShown(): bool
    {
        try {
            $areInfoBlocksShown = Utils::getBoolRepresentationOfBoolString($_ENV[self::APP_SHOW_INFO_BLOCKS]);
        } catch (\Exception $e) {
            $areInfoBlocksShown = false;
        }
        return $areInfoBlocksShown;
    }

    /**
     * Returns current environment under which the project is running
     *
     * @return string
     */
    public static function getEnvironment(): string
    {
        return $_ENV['APP_ENV'];
    }

}
