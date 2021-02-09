<?php

namespace App\DTO;

class DatabaseCredentialsDTO {

    const DATABASE_LOGIN    = 'login';
    const DATABASE_PASSWORD = 'password';
    const DATABASE_PORT     = 'port';
    const DATABASE_HOST     = 'host';
    const DATABASE_NAME     = 'name';

    /**
     * @var string
     */
    private $databaseLogin = '';

    /**
     * @var string
     */
    private $databaseHost = '';

    /**
     * @var string
     */
    private $databasePort = '';

    /**
     * @var string
     */
    private $databasePassword = '';

    /**
     * @var string
     */
    private $databaseName = '';

    /**
     * @return string
     */
    public function getDatabaseLogin(): string {
        return $this->databaseLogin;
    }

    /**
     * @param string $databaseLogin
     */
    public function setDatabaseLogin(string $databaseLogin): void {
        $this->databaseLogin = $databaseLogin;
    }

    /**
     * @return string
     */
    public function getDatabaseHost(): string {
        return $this->databaseHost;
    }

    /**
     * @param string $databaseHost
     */
    public function setDatabaseHost(string $databaseHost): void {
        $this->databaseHost = $databaseHost;
    }

    /**
     * @return string
     */
    public function getDatabasePort(): string {
        return $this->databasePort;
    }

    /**
     * @param string $databasePort
     */
    public function setDatabasePort(string $databasePort): void {
        $this->databasePort = $databasePort;
    }

    /**
     * @return string
     */
    public function getDatabasePassword(): string {
        return $this->databasePassword;
    }

    /**
     * @param string $databasePassword
     */
    public function setDatabasePassword(string $databasePassword): void {
        $this->databasePassword = $databasePassword;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName(string $databaseName): void {
        $this->databaseName = $databaseName;
    }

    /**
     * @param string $json
     * @return DatabaseCredentialsDTO
     */
    public function fromJson(string $json): self{

    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array = [
            self::DATABASE_HOST     => $this->getDatabaseHost(),
            self::DATABASE_LOGIN    => $this->getDatabaseLogin(),
            self::DATABASE_NAME     => $this->getDatabaseName(),
            self::DATABASE_PASSWORD => $this->getDatabasePassword(),
            self::DATABASE_PORT     => $this->getDatabasePort(),
        ];

        $json = json_encode($array);

        return $json;
    }

}