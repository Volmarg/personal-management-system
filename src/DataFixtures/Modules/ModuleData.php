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

        foreach(ModuleDataProvider::ALL_ENTRIES as $single_entry){
            $identifier  = $single_entry[ModuleDataProvider::KEY_RECORD_IDENTIFIER];
            $module      = $single_entry[ModuleDataProvider::KEY_MODULE];
            $type        = $single_entry[ModuleDataProvider::KEY_RECORD_TYPE];
            $description = $single_entry[ModuleDataProvider::KEY_DESCRIPTION];

            $module_data = new ModuleDataEntity();
            $module_data->setRecordIdentifier($identifier);
            $module_data->setModule($module);
            $module_data->setRecordType($type);
            $module_data->setDescription($description);

            $manager->persist($module_data);;
        }

        $manager->flush();
    }
}