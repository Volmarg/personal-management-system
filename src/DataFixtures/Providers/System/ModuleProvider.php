<?php

namespace App\DataFixtures\Providers\System;

use App\Services\Module\ModulesService;

class ModuleProvider
{

    const ALL_SUPPORTED_MODULES_NAMES = [
        ModulesService::MODULE_NAME_ISSUES,
        ModulesService::MODULE_NAME_GOALS,
    ];

}