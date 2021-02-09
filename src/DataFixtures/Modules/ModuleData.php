<?php
namespace App\DataFixtures\Modules;

use App\DataFixtures\Providers\Modules\ModuleData as ModuleDataProvider;
use App\Entity\Modules\ModuleData as ModuleDataEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ModuleData extends Fixture
{

    public function load(ObjectManager $manager)
    {

        foreach(ModuleDataProvider::ALL_ENTRIES as $singleEntry){
            $identifier  = $singleEntry[ModuleDataProvider::KEY_RECORD_IDENTIFIER];
            $module      = $singleEntry[ModuleDataProvider::KEY_MODULE];
            $type        = $singleEntry[ModuleDataProvider::KEY_RECORD_TYPE];
            $description = $singleEntry[ModuleDataProvider::KEY_DESCRIPTION];

            $moduleData = new ModuleDataEntity();
            $moduleData->setRecordIdentifier($identifier);
            $moduleData->setModule($module);
            $moduleData->setRecordType($type);
            $moduleData->setDescription($description);

            $manager->persist($moduleData);;
        }

        $manager->flush();
    }
}