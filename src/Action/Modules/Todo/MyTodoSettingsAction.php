<?php

namespace App\Action\Modules\Todo;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_TODO
 * )
 */
class MyTodoSettingsAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

}