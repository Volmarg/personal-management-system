<?php

namespace App\Repository\Modules\Storage;

use App\Entity\Modules\Storage\StorageFile;
use App\Enum\StorageModuleEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StorageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageFile[]    findAll()
 * @method StorageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageFileRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, StorageFile::class);
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function exists(string $filePath): bool
    {
        $result = $this->findOneBy([
            'filePath' => $filePath,
        ]);

        return !is_null($result);
    }

    /**
     * @param string            $oldPath
     * @param string            $newPath
     * @param StorageModuleEnum $storageModuleEnum
     */
    public function updatePath(string $oldPath, string $newPath, StorageModuleEnum $storageModuleEnum): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update(StorageFile::class, 's')
            ->where('s.filePath = :oldPath')
            ->set('s.filePath', ':newPath')
            ->set('s.moduleName', ':moduleName')
            ->setParameter('oldPath', $oldPath)
            ->setParameter('newPath', $newPath)
            ->setParameter('moduleName', $storageModuleEnum->value);

        $qb->getQuery()->execute();
    }

}
