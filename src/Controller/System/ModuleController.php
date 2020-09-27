<?php


namespace App\Controller\System;


use App\Controller\Core\Application;
use App\Entity\System\Module;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Will return all active modules;
     *
     * @return Module[]
     */
    public function getAllActive(): array
    {
        $modules = $this->app->repositories->moduleRepository->getAllActive();
        return $modules;
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