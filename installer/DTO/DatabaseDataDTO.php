<?php

namespace Installer\Controller\DTO;

/**
 * Consist of database data
 *
 * Class DatabaseDataDTO
 */
class DatabaseDataDTO
{
    const PARAM_DB_LOGIN    = "databaseLogin";
    const PARAM_DB_NAME     = "databaseName";
    const PARAM_DB_PASSWORD = "databasePassword";
    const PARAM_DB_PORT     = "databasePort";
    const PARAM_DB_HOST     = "databaseHost";

    /**
     * @var string $databaseLogin
     */
    private string $databaseLogin = "";

    /**
     * @var string $databasePassword
     */
    private string $databasePassword = "";

    /**
     * @var string $databasePort
     */
    private string $databasePort = "";

    /**
     * @var string $databaseHost
     */
    private string $databaseHost = "";

    /**
     * @var string $databaseName
     */
    private string $databaseName = "";

    /**
     * @return string
     */
    public function getDatabaseLogin(): string
    {
        return $this->databaseLogin;
    }

    /**
     * @param string $databaseLogin
     */
    public function setDatabaseLogin(string $databaseLogin): void
    {
        $this->databaseLogin = $databaseLogin;
    }

    /**
     * @return string
     */
    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    /**
     * @param string $databasePassword
     */
    public function setDatabasePassword(string $databasePassword): void
    {
        $this->databasePassword = $databasePassword;
    }

    /**
     * @return string
     */
    public function getDatabasePort(): string
    {
        return $this->databasePort;
    }

    /**
     * @param string $databasePort
     */
    public function setDatabasePort(string $databasePort): void
    {
        $this->databasePort = $databasePort;
    }

    /**
     * @return string
     */
    public function getDatabaseHost(): string
    {
        return $this->databaseHost;
    }

    /**
     * @param string $databaseHost
     */
    public function setDatabaseHost(string $databaseHost): void
    {
        $this->databaseHost = $databaseHost;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName(string $databaseName): void
    {
        $this->databaseName = $databaseName;
    }

    /**
     * Build dto from json data
     *
     * @param string $json
     * @return DatabaseDataDTO
     */
    public static function fromJson(string $json): DatabaseDataDTO
    {
        $requestData = json_decode($json, true);

        $databaseLogin    = $requestData[self::PARAM_DB_LOGIN] ?? "";
        $databasePassword = $requestData[self::PARAM_DB_PASSWORD] ?? "";
        $databasePort     = $requestData[self::PARAM_DB_PORT] ?? "";
        $databaseHost     = $requestData[self::PARAM_DB_HOST] ?? "";
        $databaseName     = $requestData[self::PARAM_DB_NAME] ?? "";

        $dto = new DatabaseDataDTO();
        $dto->setDatabaseLogin($databaseLogin);
        $dto->setDatabasePassword($databasePassword);
        $dto->setDatabasePort($databasePort);
        $dto->setDatabaseHost($databaseHost);
        $dto->setDatabaseName($databaseName);

        return $dto;
    }
}