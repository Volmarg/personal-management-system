<?php

namespace App\DataFixtures\Providers\System;

use App\Controller\Modules\ModulesController;

class ModuleProvider
{

    const ALL_SUPPORTED_MODULES_NAMES = [
        ModulesController::MODULE_NAME_ISSUES,
        ModulesController::MODULE_NAME_GOALS,
    ];

}