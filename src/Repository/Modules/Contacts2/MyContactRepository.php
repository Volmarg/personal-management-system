<?php

namespace App\Repository\Modules\Contacts2;

use App\Entity\Modules\Contacts2\MyContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContact[]    findAll()
 * @method MyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyContact::class);
    }

    /**
     * @return MyContact[]
     */
    public function findAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * This function flushes the $entity
     * @param MyContact $my_contact
     * @param bool $search_and_rebuild_entity - this flag is needed in case of persisting entity built from form data (even if the id is the same)
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function saveEntity(MyContact $my_contact, bool $search_and_rebuild_entity = false){

        if( $search_and_rebuild_entity ){
            $id                             = $my_contact->getId();
            $name                           = $my_contact->getName();
            $contacts                       = $my_contact->getContacts();
            $description                    = $my_contact->getDescription();
            $image_path                     = $my_contact->getImagePath();
            $name_background_color          = $my_contact->getNameBackgroundColor();
            $description_background_color   = $my_contact->getDescriptionBackgroundColor();

            $found_entity = $this->find($id);

            if( !empty($found_entity) ){
                $my_contact = $found_entity;
                $my_contact->setName($name);
                $my_contact->setContacts($contacts->toJson());
                $my_contact->setImagePath($image_path);
                $my_contact->setDescription($description);
                $my_contact->setNameBackgroundColor($name_background_color);
                $my_contact->setDescriptionBackgroundColor($description_background_color);
            }

        }

        $this->_em->persist($my_contact);
        $this->_em->flush();
    }

    /**
     * This function will search for single (not deleted) entity with given id
     * @param int $id
     * @return MyContact|null
     */
    public function findOneById(int $id):?MyContact {
        return $this->findOneBy(['id' => $id]);
    }

}
