<?php

namespace App\Listeners\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\FileStorageAssociationInterface;
use App\Repository\Modules\Storage\StorageFileRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

class EntityStorageFileHydrationSubscriber implements EventSubscriber
{

    public function __construct(
        private StorageFileRepository $repository
    ) {}

    public  function getSubscribedEvents(): array
    {
        return [
            Events::postLoad => 'postLoad',
            Events::preFlush => 'preFlush',
            Events::postFlush => 'postFlush',
        ];
    }

    /**
     * Loads the {@see StorageFiles} for the entity
     *
     * @param PostLoadEventArgs $args
     */
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof EntityInterface) {
            return;
        }

        $storageFiles = $this->repository->findRelated($entity);

        if (!$entity instanceof FileStorageAssociationInterface) {
            return;
        }

        $entity->setStorageFiles($storageFiles);
    }

    /**
     * Handles the entity changeset for storage file n:n relation
     *
     * @param PreFlushEventArgs $args
     *
     * @return void
     * @throws \Throwable
     */
    public function preFlush(PreFlushEventArgs $args): void
    {
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $this->handleRelations($uow);
    }

    /**
     * Handles the new entities creation for storage file n:n relation
     *
     * @param PostFlushEventArgs $args
     *
     * @return void
     * @throws \Throwable
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $this->handleRelations($uow);
    }

    /**
     * @param UnitOfWork $uow
     *
     * @return void
     * @throws \Throwable
     */
    public function handleRelations(UnitOfWork $uow): void
    {
        foreach ($uow->getIdentityMap() as $classEntities) {
            foreach ($classEntities as $entity) {
                if (!($entity instanceof FileStorageAssociationInterface)) {
                    continue;
                }

                $this->repository->handleRelationWithStorageFile($entity->getStorageFiles(), $entity);
            }
        }
    }

}