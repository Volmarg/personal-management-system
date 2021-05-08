<?php


namespace App\Controller\System;


use App\Controller\Core\Application;
use App\Entity\System\LockedResource;
use App\Entity\System\Module;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(Application $app, LockedResourceController $lockedResourceController)
    {
        $this->lockedResourceController = $lockedResourceController;
        $this->app                      = $app;
    }

    /**
     * Will return all active modules;
     *
     * @return Module[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getAllActive(): array
    {
        $modules         = $this->app->repositories->moduleRepository->getAllActive();
        $filteredModules = array_filter(
            $modules,
            fn(Module $module) => $this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $module->getName(), false)
        );

        return $filteredModules;
    }

    /**
     * Will return one module by its name
     *
     * @param string $name
     * @return Module
     */
    public function getOneByName(string $name): Module
    {
        return $this->app->repositories->moduleRepository->getOneByName($name);
    }

}