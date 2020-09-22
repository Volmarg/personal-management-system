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

}