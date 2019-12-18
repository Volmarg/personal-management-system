<?php

namespace App\Repository\Modules\Passwords;

use App\Entity\Modules\Passwords\MyPasswords;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPasswords|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPasswords|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPasswords[]    findAll()
 * @method MyPasswords[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPasswordsRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPasswords::class);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Doctrine\DBAL\DBALException
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

        $binded_params = ['id' => $id];
        $statement     = $connection->prepare($sql);
        $statement->execute($binded_params);

        $result     = $statement->fetchAll();
        $returned   = (!empty($result) ? $result[0]->password : '');

        return $returned;
    }

}