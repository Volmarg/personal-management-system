<?php

namespace App\Repository;

use App\Controller\Page\SettingsController;
use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException as DBALExceptionAlias;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    /**
     * @return string
     * @throws DBALExceptionAlias
     */
    public function fetchSettingsForDashboard():string {

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                value AS value
                
             FROM setting
             WHERE name = :key
        ";

        $params = [
            'key' => SettingsController::SETTING_NAME_DASHBOARD
        ];

        $stmt   = $connection->executeQuery($sql, $params);
        $result = $stmt->fetchColumn();

        if( false == $result ){
            return '';
        }

        return $result;
    }
}
