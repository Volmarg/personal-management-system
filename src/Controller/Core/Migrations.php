<?php


namespace App\Controller\Core;


use Exception;

class Migrations
{

    const CONSTRAINT_TYPE_FOREIGN_KEY = "FOREIGN KEY";
    const CONSTRAINT_TYPE_INDEX       = "INDEX";
    const CONSTRAINT_TYPE_UNIQUE      = "UNIQUE";

    /**
     * Will output an sql only if constraint does not exist in given table
     * - prevents crashing on cases where foreign key is about to be added but it already exists
     *
     * @param string $constraintType
     * @param string $constraintName
     * @param string $executedSql
     * @return string
     */
    public static function buildSqlExecutedIfConstraintDoesNotExist(string $constraintType, string $constraintName, string $executedSql): string
    {
        $sql = "
            SELECT IF (
                EXISTS(
                    SELECT NULL                                                                                                                                                                                     
                    FROM information_schema.TABLE_CONSTRAINTS                                                                                                                                                       
                    WHERE                                                                                                                                                                                           
                    CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
                    CONSTRAINT_NAME   = '{$constraintName}' AND                                                                                                                                               
                    CONSTRAINT_TYPE   = '{$constraintType}'                                                                                                                                                       
                )
                ,'select ''index index_1 exists'' _______;'
                ,'{$executedSql}'
            ) INTO @a;
                PREPARE executedIfConstraintNotExist FROM @a;
                EXECUTE executedIfConstraintNotExist;
                DEALLOCATE PREPARE executedIfConstraintNotExist;
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only if column in table does not exist
     * - prevents crashing on cases where column already exists
     *
     * @param string $columnName
     * @param string $tableName
     * @param string $executedSql
     * @return string
     */
    public static function buildSqlExecutedIfColumnDoesNotExist(string $columnName, string $tableName, string $executedSql): string
    {
        $sql = "
            SET @dbname             = DATABASE();
            SET @tablename          = '{$tableName}';
            SET @columnname         = '{$columnName}';
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                        (table_name   = @tablename)
                  AND   (table_schema = @dbname)
                  AND   (column_name  = @columnname)
              ) > 0,
              'SELECT 1',
              '{$executedSql}'
            ));
            PREPARE executedIfColumnNotExist FROM @preparedStatement;
            EXECUTE executedIfColumnNotExist;
            DEALLOCATE PREPARE executedIfColumnNotExist; 
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only if column in table does exist
     * - prevents crashing on cases where column does exists
     *
     * @param string $columnName
     * @param string $tableName
     * @param string $executedSql
     * @return string
     */
    public static function buildSqlExecutedIfColumnExist(string $columnName, string $tableName, string $executedSql): string
    {
        $sql = "
            SET @dbname             = DATABASE();
            SET @tablename          = '{$tableName}';
            SET @columnname         = '{$columnName}';
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                        (table_name   = @tablename)
                  AND   (table_schema = @dbname)
                  AND   (column_name  = @columnname)
              ) >= 1,
              'SELECT 1',
              '{$executedSql}'
            ));
            PREPARE executedIfColumnExist FROM @preparedStatement;
            EXECUTE executedIfColumnExist;
            DEALLOCATE PREPARE executedIfColumnExist; 
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only when given table does not exist
     * - prevents crashing on cases where table does not exist
     *
     * @param string $tableName
     * @param string $executedSql
     * @return string
     * @throws Exception
     */
    public static function buildSqlExecutedIfTableNotExist(string $tableName, string $executedSql): string
    {
        $databaseCredentialsDto = Env::getDatabaseCredentials();
        $databaseName           = $databaseCredentialsDto->getDatabaseName();

        $sql = "
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE
                            table_name   = '{$tableName}'
                        AND table_schema = '{$databaseName}'
              ) > 0,
              'SELECT 1',
              '{$executedSql}'
            ));
            PREPARE executedIfTableNotExist FROM @preparedStatement;
            EXECUTE executedIfTableNotExist;
            DEALLOCATE PREPARE executedIfTableNotExist; 
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only when given table does exist
     * - prevents crashing on cases where table does exist
     *
     * @param string $tableName
     * @param string $executedSql
     * @return string
     * @throws Exception
     */
    public static function buildSqlExecutedIfTableExist(string $tableName, string $executedSql): string
    {
        $databaseCredentialsDto = Env::getDatabaseCredentials();
        $databaseName           = $databaseCredentialsDto->getDatabaseName();

        $sql = "
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE
                            table_name   = '{$tableName}'
                        AND table_schema = '{$databaseName}'
              ) >= 1,
              'SELECT 1',
              '{$executedSql}'
            ));
            PREPARE executedIfTableExist FROM @preparedStatement;
            EXECUTE executedIfTableExist;
            DEALLOCATE PREPARE executedIfTableExist; 
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only if given table is empty
     * - prevents crashing on cases where column already exists
     *
     * @param string $tableName
     * @param string $executedSql
     * @return string
     * @throws Exception
     */
    public static function buildSqlExecutedIfTableIsEmpty(string $tableName, string $executedSql): string
    {
        $databaseCredentialsDto = Env::getDatabaseCredentials();
        $databaseName           = $databaseCredentialsDto->getDatabaseName();

        $sql = "
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT IF(TABLE_ROWS !='', TABLE_ROWS, 1) -- add 1 to prevent executing if table does not exist 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_NAME = '{$tableName}'
                AND TABLE_SCHEMA = '{$databaseName}'
              ) > 0,
              'SELECT 1',
              '{$executedSql}'              
            ));
            PREPARE executedIfTableIsEmpty FROM @preparedStatement;
            EXECUTE executedIfTableIsEmpty;
            DEALLOCATE PREPARE executedIfTableIsEmpty; 
        ";

        return $sql;
    }
}