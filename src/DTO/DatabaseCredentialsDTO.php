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
    private $database_login = '';

    /**
     * @var string
     */
    private $database_host = '';

    /**
     * @var string
     */
    private $database_port = '';

    /**
     * @var string
     */
    private $database_password = '';

    /**
     * @var string
     */
    private $database_name = '';

    /**
     * @return string
     */
    public function getDatabaseLogin(): string {
        return $this->database_login;
    }

    /**
     * @param string $database_login
     */
    public function setDatabaseLogin(string $database_login): void {
        $this->database_login = $database_login;
    }

    /**
     * @return string
     */
    public function getDatabaseHost(): string {
        return $this->database_host;
    }

    /**
     * @param string $database_host
     */
    public function setDatabaseHost(string $database_host): void {
        $this->database_host = $database_host;
    }

    /**
     * @return string
     */
    public function getDatabasePort(): string {
        return $this->database_port;
    }

    /**
     * @param string $database_port
     */
    public function setDatabasePort(string $database_port): void {
        $this->database_port = $database_port;
    }

    /**
     * @return string
     */
    public function getDatabasePassword(): string {
        return $this->database_password;
    }

    /**
     * @param string $database_password
     */
    public function setDatabasePassword(string $database_password): void {
        $this->database_password = $database_password;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string {
        return $this->database_name;
    }

    /**
     * @param string $database_name
     */
    public function setDatabaseName(string $database_name): void {
        $this->database_name = $database_name;
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