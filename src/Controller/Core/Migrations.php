<?php


namespace App\Controller\Core;


use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 *
 * Class Migrations
 * @package App\Controller\Core
 */
class Migrations
{

    const CONSTRAINT_TYPE_FOREIGN_KEY = "FOREIGN KEY";
    const CONSTRAINT_TYPE_INDEX       = "INDEX";
    const CONSTRAINT_TYPE_UNIQUE      = "UNIQUE";

    const MYSQL_VAR_NAME_EXECUTED_STMT = "executedStatement";

    /**
     * Will output an sql only if constraint does not exist in given table
     * - prevents crashing on cases where constraint is about to be added but it already exists
     *
     * @param string $constraintType
     * @param string $constraintName
     * @param string $executedSql
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    public static function buildSqlExecutedIfConstraintDoesNotExist(string $constraintType, string $constraintName, string $executedSql): string
    {
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;

        $selectConstraintFromInformationSchemaSql = "
            SELECT NULL                                                                                                                                                                                     
            FROM information_schema.TABLE_CONSTRAINTS                                                                                                                                                       
            WHERE                                                                                                                                                                                           
            CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
            CONSTRAINT_NAME   = \"{$constraintName}\" AND                                                                                                                                               
            CONSTRAINT_TYPE   = \"{$constraintType}\"              
        ";

        $selectConstraintFromInformationSchemaExtensionSql = "
            SELECT NULL                                          
            FROM information_schema.TABLE_CONSTRAINTS_EXTENSIONS                                                                                                                                                       
            WHERE                                                                                                                                                                                           
            CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
            CONSTRAINT_NAME   = \"{$constraintName}\"
        ";

        $setStatementIntoVariableSql = "
            SELECT IF (
                EXISTS(
                    -- depending on DB configuration/versions the `TABLE_CONSTRAINTS_EXTENSIONS` might be missing
                    SELECT IF (
                        EXISTS (
                            SELECT 1 FROM INFORMATION_SCHEMA.TABLES   
                            WHERE TABLE_SCHEMA = 'information_schema' 
                              AND TABLE_NAME = 'TABLE_CONSTRAINTS_EXTENSIONS'
                        ),
                        '{$selectConstraintFromInformationSchemaSql}
                          UNION
                         {$selectConstraintFromInformationSchemaExtensionSql}
                        ',
                        '{$selectConstraintFromInformationSchemaSql}'
                    )
                )
                ,'select ''index index_1 exists'' _______;'
                ,'{$executedSql}'
            ) INTO @{$stmtVariableName};
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfConstraintNotExist FROM @{$stmtVariableName};
            EXECUTE executedIfConstraintNotExist;
            DEALLOCATE PREPARE executedIfConstraintNotExist;
        ";

        return $sql;
    }

    /**
     * Will output an sql only if constraint does exist in given table
     * - prevent crashing when for example trying to remove not existing constraint
     *
     * @param string $constraintType
     * @param string $constraintName
     * @param string $executedSql
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    public static function buildSqlExecutedIfConstraintDoesExist(string $constraintType, string $constraintName, string $executedSql): string
    {
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SELECT IF (
                EXISTS(
                    SELECT NULL                                                                                                                                                                                     
                    FROM information_schema.TABLE_CONSTRAINTS                                                                                                                                                       
                    WHERE                                                                                                                                                                                           
                    CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
                    CONSTRAINT_NAME   = '{$constraintName}' AND                                                                                                                                               
                    CONSTRAINT_TYPE   = '{$constraintType}'            
                    
                    UNION 
                    
                    SELECT NULL                                          
                    FROM information_schema.TABLE_CONSTRAINTS_EXTENSIONS                                                                                                                                                       
                    WHERE                                                                                                                                                                                           
                    CONSTRAINT_SCHEMA = DATABASE() AND                                                                                                                                                          
                    CONSTRAINT_NAME   = '{$constraintName}'                        
                )
                ,'{$executedSql}'
                ,'select ''index index_1 exists'' _______;'
            ) INTO @{$stmtVariableName};
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfConstraintExist FROM @{$stmtVariableName};
            EXECUTE executedIfConstraintExist;
            DEALLOCATE PREPARE executedIfConstraintExist;
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
     * @throws \Doctrine\DBAL\Exception
     */
    public static function buildSqlExecutedIfColumnDoesNotExist(string $columnName, string $tableName, string $executedSql): string
    {
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SET @{$stmtVariableName}  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                        (table_name   = '{$tableName}')
                  AND   (table_schema = DATABASE())
                  AND   (column_name  = '{$columnName}')
              ) = 0,
              '{$executedSql}',
              'SELECT 1'
            ));
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfColumnNotExist FROM @{$stmtVariableName};
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
     * @throws \Doctrine\DBAL\Exception
     */
    public static function buildSqlExecutedIfColumnExist(string $columnName, string $tableName, string $executedSql): string
    {
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SET @{$stmtVariableName}  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                        (table_name   = '{$tableName}')
                  AND   (table_schema = DATABASE())
                  AND   (column_name  = '{$columnName}')
              ) >= 1,
              '{$executedSql}',
              'SELECT 1'
            ));
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfColumnExist FROM @{$stmtVariableName};
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
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SET @{$stmtVariableName}  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE
                            table_name   = '{$tableName}'
                        AND table_schema = DATABASE()
              ) > 0,
              'SELECT 1',
              '{$executedSql}'
            ));
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfTableNotExist FROM @{$stmtVariableName};
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
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SET @{$stmtVariableName}  = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE
                            table_name   = '{$tableName}'
                        AND table_schema = DATABASE()
              ) >= 1,
              '{$executedSql}',
              'SELECT 1'
            ));
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfTableExist FROM @{$stmtVariableName};
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
        $stmtVariableName            = self::MYSQL_VAR_NAME_EXECUTED_STMT;
        $setStatementIntoVariableSql = "
            SET @{$stmtVariableName}  = (SELECT IF(
              (
                SELECT IF(TABLE_ROWS !='', TABLE_ROWS, 1) -- add 1 to prevent executing if table does not exist 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_NAME = '{$tableName}'
                AND TABLE_SCHEMA = DATABASE()
              ) > 0,
              'SELECT 1',
              '{$executedSql}'              
            ));
        ";

        self::testCalledSql($executedSql, $setStatementIntoVariableSql);

        $sql = "
            {$setStatementIntoVariableSql}
            PREPARE executedIfTableIsEmpty FROM @{$stmtVariableName};
            EXECUTE executedIfTableIsEmpty;
            DEALLOCATE PREPARE executedIfTableIsEmpty; 
        ";

        return $sql;
    }

    /**
     * The logic for calling migrations was changed due to usage of stmts, now the sqls are being called directly in the migration
     * `addSql` is no longer used, thus due to missing addition Doctrine shows "No sql was executed".
     *
     * This method will return sql with success information and will mute that error when called.
     *
     * @return string
     */
    public static function getSuccessInformationSql(): string
    {
        return "SELECT 'Migration executed with success' FROM DUAL";
    }

    /**
     * This method tests the sql used within prepared statement, it uses transaction so nothing gets committed to the DB
     * It's required due to the fact that Doctrine will always say "success" even if the sql in stmt fails,
     *
     * @param string $sql
     * @param string $statementSql
     * @throws \Doctrine\DBAL\Exception
     */
    private static function testCalledSql(string $sql, string $statementSql)
    {
        $kernel = new Kernel(Env::getEnvironment(), false);
        $kernel->boot();

        /**
         * @var Application $app
         * @var EntityManagerInterface $em
         */
        $app        = $kernel->getContainer()->get('App\Controller\Core\Application');
        $em         = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $em->getConnection();

        try{
            $em->beginTransaction();
            {
                // this is required, doctrine does not allow to execute multiple sql in single call
                $connection->executeQuery($statementSql);
                $connection->executeQuery("PREPARE testStatement FROM @" . self::MYSQL_VAR_NAME_EXECUTED_STMT);
                $connection->executeQuery("EXECUTE testStatement");
                $connection->executeQuery("DEALLOCATE PREPARE testStatement");
            }
            $em->rollback();
        }catch(Exception $e){
            $em->rollback();
            $app->logExceptionWasThrown($e);
            $app->logger->warning("Related SQL: ", [
                "sql" => $sql,
            ]);
            throw $e;
        }
    }

}