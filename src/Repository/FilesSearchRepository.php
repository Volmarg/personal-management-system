<?php

namespace App\Repository;

use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManagerInterface;

class FilesSearchRepository
{

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @param array $tags
     * @param bool $do_like_percent
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSearchResultsDataForTag(array $tags, bool $do_like_percent = false) {

        $binded_values = [];
        $index         = 0;

        $tags_sql      = '';

        array_map(function($value) use ($do_like_percent, &$binded_values,  &$tags_sql, &$index) {
            $binded_values[]       = ( $do_like_percent ? "%{$value}%" : $value );
            $tags_sql             .= (0 === $index ? " " : " OR ") . " tags LIKE ?";
            $index++;
        }, $tags);

        $connection = $this->em->getConnection();

        $sql = "
            SELECT
                ft.tags                      AS tags,
                ft.full_file_path            AS fullFilePath,
                files_tags_module.module     AS module,
                files_tags_filename.filename AS filename,
            CONCAT(  '/', -- to have absolute path  
                    CASE -- here add part of url pointing to the module name
                        WHEN full_file_path LIKE '%images%' THEN 'my-images'
                        WHEN full_file_path LIKE '%files%' THEN 'my-files'
                    END,
                    '/dir/', 
                REPLACE( -- here strip all the unnecsary slashes and upload dirs
                    CASE
                        WHEN full_file_path LIKE '%images%' THEN 
                            REPLACE(
                                REPLACE(
                                     ft.full_file_path, CONCAT('/', files_tags_filename.filename) , ''),
                                     IF( -- if main dir then strip is different
                                        LOCATE('upload/images/', REPLACE( ft.full_file_path, CONCAT('/', files_tags_filename.filename) , '')) = 0 , 'upload/images', 'upload/images/'
                                      ),
                                    ''
                            )
                    WHEN full_file_path LIKE '%files%' THEN 
                        REPLACE(
                            REPLACE(
                                ft.full_file_path, CONCAT('/', files_tags_filename.filename) , ''),
                                IF( -- if main dir then strip is different
                                    LOCATE('upload/files/', REPLACE( ft.full_file_path, CONCAT('/', files_tags_filename.filename) , '')) = 0 , 'upload/files', 'upload/files/'
                                 ),
                                ''
                        )
                    END , 
                    '/', '%252F') -- this is needed for routes, as this is encoded slash
                )
                AS directoryPath
            FROM files_tags ft
            
            JOIN -- get module name
            (
                SELECT
                    id AS id,
                CASE
                    WHEN full_file_path LIKE '%images%' THEN 'My Images'
                    WHEN full_file_path LIKE '%files%' THEN 'My Files'
                END AS module
                
                FROM files_tags
            
            ) AS files_tags_module
            ON files_tags_module.id = ft.id 
            
            JOIN -- get filename
            (
            SELECT
                id AS id,
            SUBSTRING(
                full_file_path, - LOCATE('/', REVERSE(full_file_path)) +1
            ) AS filename
            
            FROM files_tags
            
            ) AS files_tags_filename
            ON files_tags_filename.id = ft.id

            
            WHERE 1
                AND ($tags_sql) -- limit to entered tags
                AND deleted = 0;
        ";

        $stmt          = $connection->executeQuery($sql, $binded_values);
        $results       = $stmt->fetchAll();

        return $results;
    }

}
