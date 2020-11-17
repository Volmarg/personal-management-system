<?php

namespace App\Twig\Modules;

use App\Controller\Core\Controllers;
use App\Entity\Modules\ModuleData as ModuleDataEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleData extends AbstractExtension
{

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Controllers $controllers)
    {
        $this->controllers = $controllers;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getOneByRecordTypeModuleAndRecordIdentifier', [$this, 'getOneByRecordTypeModuleAndRecordIdentifier'])
        ];
   }

    /**
     * Will return single module data for given parameters, or null if nothing is found
     *
     * @param string $record_type
     * @param string $module
     * @param string $record_identifier
     * @return ModuleDataEntity|null
     */
   public function getOneByRecordTypeModuleAndRecordIdentifier(string $record_type, string $module, string $record_identifier): ?ModuleDataEntity
   {
       return $this->controllers->getModuleDataController()->getOneByRecordTypeModuleAndRecordIdentifier($record_type, $module, $record_identifier);
   }

}