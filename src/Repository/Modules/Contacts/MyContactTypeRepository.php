<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContactType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContactType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactType[]    findAll()
 * @method MyContactType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactTypeRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyContactType::class);
    }

    /**
     * @return MyContactType[]
     */
    public function getAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * @param string $name
     * @return MyContactType|null
     */
    public function getOneNonDeletedByName(string $name):?MyContactType {
        return $this->findOneBy( ["name" => $name] );
    }

    /**
     * Returns image path for contactType by it's id
     * @param string $id
     * @return false|mixed
     * @throws DBALException
     */
    public function getImagePathForTypeById(string $id){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT image_path
            FROM my_contact_type
            WHERE id = :id
        ";

        $params = [
          "id" => $id
        ];

        $stmt   = $connection->executeQuery($sql, $params);
        $result = $stmt->fetchColumn();

        return $result;
    }

    /**
     * Returns type name for contactType by it's id
     * @param string $id
     * @return false|mixed
     * @throws DBALException
     */
    public function getTypeNameTypeById(string $id){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT name
            FROM my_contact_type
            WHERE id = :id
        ";

        $params = [
            "id" => $id
        ];

        $stmt   = $connection->executeQuery($sql, $params);
        $result = $stmt->fetchColumn();

        return $result;
    }


}