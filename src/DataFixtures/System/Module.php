<?php


namespace App\DataFixtures\System;

use App\DataFixtures\Providers\System\ModuleProvider;
use App\Entity\System\Module as ModuleEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Module
 * @package App\DataFixtures\System
 */
class Module extends Fixture
{

    public function load(ObjectManager $manager)
    {

        foreach(ModuleProvider::ALL_SUPPORTED_MODULES_NAMES as $moduleName){

            $module = new ModuleEntity();
            $module->setName($moduleName);
            $module->setActive(true);

            $manager->persist($module);
        }

        $manager->flush();
    }
}