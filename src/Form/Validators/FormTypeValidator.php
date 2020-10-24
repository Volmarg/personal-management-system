<?php

namespace App\Form\Validators;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;

class FormTypeValidator
{
    /**
     * @var Application $app
     */
    protected Application $app;

    /**
     * @var Controllers
     */
    protected Controllers $controllers;

    /**
     * AbstractValidator constructor.
     * @param Application $app
     * @param Controllers $controllers
     */
    public function __construct(Application $app, Controllers  $controllers)
    {
        $this->app         = $app;
        $this->controllers = $controllers;
    }
}
