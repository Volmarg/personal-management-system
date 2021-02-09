<?php

namespace App\Repository\Modules\Passwords;

use App\Entity\Modules\Passwords\MyPasswords;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPasswords|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPasswords|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPasswords[]    findAll()
 * @method MyPasswords[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPasswordsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPasswords::class);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getPasswordForId(int $id) {

        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_OBJ);

        $sql = "
            SELECT password
            FROM my_password
            WHERE id = :id
            AND deleted = 0
        ";

        $bindedParams = ['id' => $id];
        $statement    = $connection->prepare($sql);
        $statement->execute($bindedParams);

        $result     = $statement->fetchAll();
        $returned   = (!empty($result) ? $result[0]->password : '');

        return $returned;
    }

    /**
     * Will return one entity for given id, or null otherwise
     *
     * @param int $id
     * @return MyPasswords|null
     */
    public function findPasswordEntityById(int $id): ?MyPasswords
    {
        return $this->find($id);
    }

    /**
     * @return MyPasswords[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }
}