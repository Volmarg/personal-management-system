<?php

namespace App\Repository\Modules\Storage;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\FileStorageAssociationInterface;
use App\Entity\Modules\Storage\StorageFile;
use App\Entity\Modules\Storage\StorageFile2Module;
use App\Enum\StorageModuleEnum;
use App\Listeners\Entity\EntityStorageFileHydrationSubscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Throwable;

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
    public function updateByFilePath(string $oldPath, string $newPath, StorageModuleEnum $storageModuleEnum): void
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

    /**
     * @param string $oldDir
     * @param string $newDir
     */
    public function updateForDirRename(string $oldDir, string $newDir): void
    {
        $qb = $this->_em->createQueryBuilder();

        /** @var StorageFile[] $matches */
        $matches = $qb->select("c")
            ->from(StorageFile::class, 'c')
            ->where("c.filePath LIKE :oldDir")
            ->setParameter('oldDir', "%$oldDir%")
            ->getQuery()
            ->execute();

        foreach ($matches as $match) {
            $qb = $this->_em->createQueryBuilder();
            $newPath = str_replace($oldDir, $newDir, $match->getFilePath());

            $qb->update(StorageFile::class, 's')
               ->where('s.filePath = :oldPath')
               ->set('s.filePath', ':newPath')
               ->setParameter('oldPath', $match->getFilePath())
               ->setParameter('newPath', $newPath);

            $qb->getQuery()->execute();
        }
    }

    /**
     * @param EntityInterface $entity
     *
     * @return StorageFile[]
     */
    public function findRelated(EntityInterface $entity): array
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select("s")
           ->from(StorageFile::class, 's')
           ->join(StorageFile2Module::class, "sf2m", "WITH", "s.id = IDENTITY(sf2m.storageFile)")
           ->where("sf2m.relatedModuleId = :moduleId")
           ->andWhere("sf2m.relatedModuleClass = :moduleClass")
           ->setParameter('moduleId', $entity->getId())
           ->setParameter('moduleClass', $entity::class)
           ->getQuery()
           ->execute();
    }

    /**
     * @param array                           $storageFiles
     * @param FileStorageAssociationInterface $entity
     *
     * @throws Throwable
     */
    public function handleRelationWithStorageFile(array $storageFiles, FileStorageAssociationInterface $entity): void
    {
        if (!method_exists($entity, 'getId')) {
            throw new Exception("Entity does not have getId function");
        }

        $this->_em->beginTransaction();
        try {
             $this->_em->createQueryBuilder()
                ->delete(StorageFile2Module::class, 'sf2m')
                ->where("sf2m.relatedModuleId = :relatedModuleId")
                ->andWhere("sf2m.relatedModuleClass = :relatedModuleClass")
                ->setParameter('relatedModuleId', $entity->getId())
                ->setParameter('relatedModuleClass', $entity::class)
                ->getQuery()
                ->execute();

            /**
             * This has to be sadly done RAW, else we run into circular calls
             * - {@see EntityStorageFileHydrationSubscriber}
             *
             * Doctrine has no native support for polymorphic relations so this is kinda of a hacky now
             */
            $query = "
                INSERT INTO storage_file_2_module 
                (related_module_id, storage_file_id, related_module_class, created, modified) VALUES (
                :related_module_id, :storage_file_id, :related_module_class, :now, :now
                )
            ";

            $now = (new \DateTime())->format('Y-m-d H:i:s');
            foreach ($storageFiles as $file) {
                $params = [
                    'related_module_id' => $entity->getId(),
                    'related_module_class' => $entity::class,
                    'storage_file_id' => $file->getId(),
                    'now' => $now,
                ];

                $this->_em->getConnection()->executeQuery($query, $params);
            }
        } catch (Throwable $e) {
            $this->_em->rollback();
            throw $e;
        }
        $this->_em->commit();
    }

}
