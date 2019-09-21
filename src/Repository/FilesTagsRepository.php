<?php

namespace App\Repository;

use App\Entity\FilesTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FilesTags|null find($id, $lockMode = null, $lockVersion = null)
 * @method FilesTags|null findOneBy(array $criteria, array $orderBy = null)
 * @method FilesTags[]    findAll()
 * @method FilesTags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilesTagsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FilesTags::class);
    }

    public function getFileTagsEntityByFileFullPath(string $file_full_path): ?FilesTags {
        $files_tags     = $this->_em->getRepository(FilesTags::class)->findBy(['fullFilePath' => $file_full_path]);

        if( empty($files_tags) ){
            return null;
        }else{
            $file_tags = reset($files_tags);
            return $file_tags;
        }

    }

    /**
     * @param string $old_folder_path
     * @param string $new_folder_path
     * @throws DBALException
     */
    public function updateFilePathByFolderPathChange(string $old_folder_path, string $new_folder_path): void {

        $connection = $this->_em->getConnection();

        $sql = "
            UPDATE files_tags
                SET full_file_path = REPLACE(full_file_path,:old_folder_path, :new_folder_path)
            WHERE 1
                AND deleted = 0 
        ";

        $binded_values = [
            'old_folder_path' => $old_folder_path,
            'new_folder_path' => $new_folder_path
        ];

        $connection->executeQuery($sql, $binded_values);
    }


}
