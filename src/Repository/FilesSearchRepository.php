<?php

namespace App\Repository;

use App\Controller\Modules\ModulesController;
use App\Controller\System\FilesSearchController;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Todo: use dto searchResult, make interface with common fields like module, record etc to check lock more properly
 * Class FilesSearchRepository
 * @package App\Repository
 */
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
     * @param string $searchType
     * @param bool $doLikePercent
     * @return mixed[]
     * @throws DBALException
     * @throws Exception
     */
    public function getSearchResultsDataForTag(array $tags, string $searchType, bool $doLikePercent = false) {

        $bindedValues = [];
        $index         = 0;

        $tagsSql      = '';

        array_map(function($value) use ($doLikePercent, &$bindedValues,  &$tagsSql, &$index, $searchType) {
            $bindedValues[]       = ( $doLikePercent ? "%{$value}%" : $value );

            switch($searchType){
                case FilesSearchController::SEARCH_TYPE_FILES:
                    $tagsSql .= (0 === $index ? " " : " OR ") . " tags LIKE ?";
                    break;
                case FilesSearchController::SEARCH_TYPE_NOTES:
                    $tagsSql .= (0 === $index ? " " : " OR ") . " title LIKE ?";
                    break;
                default:
                    throw new Exception("Undefined search type for search results: {$searchType}.");
            }

            $index++;
        }, $tags);

        $connection = $this->em->getConnection();

        switch($searchType){
            case FilesSearchController::SEARCH_TYPE_FILES:
                $sql = $this->getSqlForFileSearch($tagsSql);
                break;
            case FilesSearchController::SEARCH_TYPE_NOTES:
                $sql = $this->getSqlForNotesSearch($tagsSql);
                break;
            default:
                throw new Exception("Undefined search type for search results: {$searchType}.");
        }

        $stmt    = $connection->executeQuery($sql, $bindedValues);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param string $tagsSql
     * @return string
     */
    private function getSqlForFileSearch(string $tagsSql){

        $myImagesModuleName = ModulesController::MODULE_NAME_IMAGES;
        $myVideoModuleName  = ModulesController::MODULE_NAME_VIDEO;
        $myFilesModuleName  = ModulesController::MODULE_NAME_FILES;

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
                        WHEN full_file_path LIKE '%videos%' THEN 'my-video'
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
                        WHEN full_file_path LIKE '%videos%' THEN 
                            REPLACE(
                                REPLACE(
                                    ft.full_file_path, CONCAT('/', files_tags_filename.filename) , ''),
                                    IF( -- if main dir then strip is different
                                        LOCATE('upload/videos/', REPLACE( ft.full_file_path, CONCAT('/', files_tags_filename.filename) , '')) = 0 , 'upload/videos', 'upload/videos/'
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
                    WHEN full_file_path LIKE '%images%' THEN '{$myImagesModuleName}'
                    WHEN full_file_path LIKE '%files%' THEN '{$myFilesModuleName}'
                    WHEN full_file_path LIKE '%videos%' THEN '{$myVideoModuleName}'
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
                AND ($tagsSql) -- limit to entered tags
                AND deleted = 0;
        ";

        return $sql;
    }

    /**
     * @param string $tagsSql
     * @return string
     */
    private function getSqlForNotesSearch(string $tagsSql){
        $notesModuleName = ModulesController::MODULE_NAME_NOTES;

        $sql = "
            SELECT 
                mn.title             AS title,
                mn.id                AS noteId,
                mnc.name             AS category,
                mnc.id               AS categoryId,
                'note'               AS type,
                '{$notesModuleName}' AS module  
            
            FROM my_note mn
            
            LEFT JOIN my_note_category mnc
                ON mnc.id = mn.category_id
                AND mnc.deleted = 0
            
            WHERE 1
                AND ($tagsSql) -- limit to entered tags
                AND mn.deleted = 0;
        ";

        return $sql;
    }

}
