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
                CASE
                    WHEN full_file_path LIKE '%images%' THEN 'My Image'
                    WHEN full_file_path LIKE '%files%' THEN 'My Files'
                END AS module,
            SUBSTRING(
                full_file_path, - LOCATE('/', REVERSE(full_file_path)) +1
            ) AS filename,
            full_file_path AS fullFilePath,
            tags AS tags
            
            FROM files_tags
            
            WHERE 1
                AND ($tags_sql)
                AND deleted = 0;
        ";

        $stmt          = $connection->executeQuery($sql, $binded_values);
        $results       = $stmt->fetchAll();

        return $results;
    }

}
