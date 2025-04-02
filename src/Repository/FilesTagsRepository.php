<?php

namespace App\Repository;

use App\Entity\FilesTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FilesTags|null find($id, $lockMode = null, $lockVersion = null)
 * @method FilesTags|null findOneBy(array $criteria, array $orderBy = null)
 * @method FilesTags[]    findAll()
 * @method FilesTags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilesTagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FilesTags::class);
    }

    /**
     * Will return tags entity for given file path if exists, or null if does not
     *
     * @param string $fileFullPath
     * @return FilesTags|null
     */
    public function getFileTagsEntityByFileFullPath(string $fileFullPath): ?FilesTags {
        $filesTags = $this->_em->getRepository(FilesTags::class)->findBy(['fullFilePath' => $fileFullPath]);

        if( empty($filesTags) ){
            return null;
        }else{
            $fileTags = reset($filesTags);
            return $fileTags;
        }

    }

    /**
     * @param string $oldFolderPath
     * @param string $newFolderPath
     * @throws DBALException
     */
    public function updateFilePathByFolderPathChange(string $oldFolderPath, string $newFolderPath): void {

        $connection = $this->_em->getConnection();

        $sql = "
            UPDATE files_tags
                SET full_file_path = REPLACE(full_file_path,:old_folder_path, :new_folder_path)
            WHERE 1
                AND deleted = 0 
        ";

        $bindedValues = [
            'old_folder_path' => $oldFolderPath,
            'new_folder_path' => $newFolderPath
        ];

        $connection->executeQuery($sql, $bindedValues);
    }

    /**
     * First fetching all data by LIKE %, to reduce amount of fetched entries,
     * then looping over entities to get the entries that start with dir path.
     *
     * @param string $dirPath
     *
     * @return FilesTags[]
     */
    public function findByDirPath(string $dirPath): array
    {

        $qb = $this->_em->createQueryBuilder();
        $qb->select("ft")
           ->from(FilesTags::class, 'ft')
           ->where(
               $qb->expr()->like("ft.fullFilePath", ":dirPath")
           )->setParameter("dirPath", "%" . $dirPath . "%");

        /** @var FilesTags[] $entries */
        $entries = $qb->getQuery()->getResult();
        if (empty($entries)) {
            return [];
        }

        $filtered = [];
        foreach ($entries as $entry) {
            if (str_starts_with($entry->getFullFilePath(), $dirPath)) {
                $filtered[] = $entry;
            }
        }

        return $filtered;
    }
}
