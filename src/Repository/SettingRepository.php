<?php

namespace App\Repository;

use App\Entity\Setting;
use App\Services\Settings\SettingsLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @return Setting|null
     */
    public function getSettingsForDashboard():?Setting {
        $result = $this->findBy(['name' => SettingsLoader::SETTING_NAME_DASHBOARD]);

        if( empty($result) ){
            return null;
        }

        return reset($result);
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForFinances():?Setting {
        $result = $this->findBy(['name' => SettingsLoader::SETTING_NAME_FINANCES]);

        if( empty($result) ){
            return null;
        }

        return reset($result);
    }
}
