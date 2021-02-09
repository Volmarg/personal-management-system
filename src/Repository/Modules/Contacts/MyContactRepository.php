<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method MyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContact[]    findAll()
 * @method MyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
     * @param MyContact $myContact
     * @param bool $searchAndRebuildEntity - this flag is needed in case of persisting entity built from form data (even if the id is the same)
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function saveEntity(MyContact $myContact, bool $searchAndRebuildEntity = false){

        if( $searchAndRebuildEntity ){
            $foundEntity = null;

            $id                         = $myContact->getId();
            $name                       = $myContact->getName();
            $group                      = $myContact->getGroup();
            $contacts                   = $myContact->getContacts();
            $description                = $myContact->getDescription();
            $imagePath                  = $myContact->getImagePath();
            $nameBackgroundColor        = $myContact->getNameBackgroundColor();
            $descriptionBackgroundColor = $myContact->getDescriptionBackgroundColor();

            // new entity
            if( !empty($id) ){
                $foundEntity = $this->find($id);
            }

            //updated
            if( !empty($foundEntity) ){
                $myContact = $foundEntity;
                $myContact->setName($name);
                $myContact->setGroup($group);
                $myContact->setContacts($contacts->toJson());
                $myContact->setImagePath($imagePath);
                $myContact->setDescription($description);
                $myContact->setNameBackgroundColor($nameBackgroundColor);
                $myContact->setDescriptionBackgroundColor($descriptionBackgroundColor);
            }

        }

        $this->_em->persist($myContact);
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

    /**
     * This function will return the contacts that contain give contact type in contacts json
     * @param string $contactTypeName
     * @return MyContact[]|null
     */
    public function findContactsWithContactTypeByContactTypeName(string $contactTypeName): ?array
    {
        $qb = $this->createQueryBuilder("mc");
        $qb->select("mc")
            ->where('mc.contacts LIKE :contact_type_name')
            ->andWhere("mc.deleted = 0")
            ->setParameter('contact_type_name', '%"name":"'. $contactTypeName .'"%');

        $query   = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
