<?php

namespace App\Action\Modules\Todo;

use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// todo
//  - handle binding of modules  maybe, or at least make it read only at this point
class MyTodoSettingsAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

}