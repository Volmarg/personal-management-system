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
     * @param string $constraint_type
     * @param string $constraint_name
     * @param string $executed_sql
     * @return string
     */
    public static function buildSqlExecutedIfConstraintDoesNotExist(string $constraint_type, string $constraint_name, string $executed_sql): string
    {
        $sql = "
            SELECT IF (
                EXISTS(
                    SELECT NULL                                                                                                                                                                                     
                    FROM information_schema.TABLE_CONSTRAINTS                                                                                                                                                       
                    WHERE                                                                                                                                                                                           
                    CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
                    CONSTRAINT_NAME   = '{$constraint_name}' AND                                                                                                                                               
                    CONSTRAINT_TYPE   = '{$constraint_type}'                                                                                                                                                       
                )
                ,'select ''index index_1 exists'' _______;'
                ,'{$executed_sql}'
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
     * @param string $column_name
     * @param string $table_name
     * @param string $executed_sql
     * @return string
     */
    public static function buildSqlExecutedIfColumnDoesNotExist(string $column_name, string $table_name, string $executed_sql): string
    {
        $sql = "
            SET @dbname             = DATABASE();
            SET @tablename          = '{$table_name}';
            SET @columnname         = '{$column_name}';
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                        (table_name   = @tablename)
                  AND   (table_schema = @dbname)
                  AND   (column_name  = @columnname)
              ) > 0,
              'SELECT 1',
              {$executed_sql}}
            ));
            PREPARE executedIfColumnNotExist FROM @preparedStatement;
            EXECUTE executedIfColumnNotExist;
            DEALLOCATE PREPARE executedIfColumnNotExist; 
        ";

        return $sql;
    }
    
    /**
     * Will output an sql executed only when given table does not exist
     * - prevents crashing on cases where column already exists
     *
     * @param string $table_name
     * @param string $executed_sql
     * @return string
     * @throws Exception
     */
    public static function buildSqlExecutedIfTableNotExist(string $table_name, string $executed_sql): string
    {
        $database_credentials_dto = Env::getDatabaseCredentials();
        $database_name            = $database_credentials_dto->getDatabaseName();

        $sql = "
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE
                            table_name   = '{$table_name}'
                        AND table_schema = '{$database_name}'
              ) > 0,
              'SELECT 1',
              '{$executed_sql}'
            ));
            PREPARE executedIfTableNotExist FROM @preparedStatement;
            EXECUTE executedIfTableNotExist;
            DEALLOCATE PREPARE executedIfTableNotExist; 
        ";

        return $sql;
    }

    /**
     * Will output an sql executed only if given table is empty
     * - prevents crashing on cases where column already exists
     *
     * @param string $table_name
     * @param string $executed_sql
     * @return string
     * @throws Exception
     */
    public static function buildSqlExecutedIfTableIsEmpty(string $table_name, string $executed_sql): string
    {
        $database_credentials_dto = Env::getDatabaseCredentials();
        $database_name            = $database_credentials_dto->getDatabaseName();

        $sql = "
            SET @preparedStatement  = (SELECT IF(
              (
                SELECT IF(TABLE_ROWS !='', TABLE_ROWS, 1) -- add 1 to prevent executing if table does not exist 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_NAME = '{$table_name}'
                AND TABLE_SCHEMA = '{$database_name}'
              ) > 0,
              'SELECT 1',
              '{$executed_sql}'              
            ));
            PREPARE executedIfTableIsEmpty FROM @preparedStatement;
            EXECUTE executedIfTableIsEmpty;
            DEALLOCATE PREPARE executedIfTableIsEmpty; 
        ";

        return $sql;
    }
}