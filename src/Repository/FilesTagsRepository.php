<?php

namespace App\Repository;

use App\Entity\FilesTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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


}
