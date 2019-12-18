<?php

namespace App\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class FilesSearchRepository
{

    const SEARCH_TYPE_FILES = 'files';
    const SEARCH_TYPE_NOTES = 'notes';

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @param array $tags
     * @param string $search_type
     * @param bool $do_like_percent
     * @return mixed[]
     * @throws DBALException
     * @throws Exception
     */
    public function getSearchResultsDataForTag(array $tags, string $search_type, bool $do_like_percent = false) {

        $binded_values = [];
        $index         = 0;

        $tags_sql      = '';

        array_map(function($value) use ($do_like_percent, &$binded_values,  &$tags_sql, &$index, $search_type) {
            $binded_values[]       = ( $do_like_percent ? "%{$value}%" : $value );

            switch($search_type){
                case self::SEARCH_TYPE_FILES:
                    $tags_sql .= (0 === $index ? " " : " OR ") . " tags LIKE ?";
                    break;
                case self::SEARCH_TYPE_NOTES:
                    $tags_sql .= (0 === $index ? " " : " OR ") . " title LIKE ?";
                    break;
                default:
                    throw new Exception("Undefined search type for search results: {$search_type}.");
            }

            $index++;
        }, $tags);

        $connection = $this->em->getConnection();

        switch($search_type){
            case self::SEARCH_TYPE_FILES:
                $sql = $this->getSqlForFileSearch($tags_sql);
                break;
            case self::SEARCH_TYPE_NOTES:
                $sql = $this->getSqlForNotesSearch($tags_sql);
                break;
            default:
                throw new Exception("Undefined search type for search results: {$search_type}.");
        }

        $stmt    = $connection->executeQuery($sql, $binded_values);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param string $tags_sql
     * @return string
     */
    private function getSqlForFileSearch(string $tags_sql){
        $sql = "
            SELECT
                ft.tags                      AS tags,
                ft.full_file_path            AS fullFilePath,
                files_tags_module.module     AS module,
                files_tags_filename.filename AS filename,
                'file'                       AS type,
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

        return $sql;
    }

    /**
     * @param string $tags_sql
     * @return string
     */
    private function getSqlForNotesSearch(string $tags_sql){
        $sql = "
            SELECT 
                mn.title    AS title,
                mn.id       AS noteId,
                mnc.name    AS category,
                mnc.id      AS categoryId,
                'note'      AS type
            
            FROM my_note mn
            
            LEFT JOIN my_note_category mnc
                ON mnc.id = mn.category_id
                AND mnc.deleted = 0
            
            WHERE 1
                AND ($tags_sql) -- limit to entered tags
                AND mn.deleted = 0;
        ";

        return $sql;
    }

}
