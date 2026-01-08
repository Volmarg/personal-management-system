<?php

namespace App\Services\Module\Dashboard;

use App\Entity\Setting;
use App\Services\Module\ModulesService;
use App\Services\Settings\SettingsLoader;
use App\Services\System\LockedResourceService;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use LogicException;

class DashboardService
{
    private const array WIDGET_TO_MODULE_NAME = [
        Setting::DASHBOARD_WIDGET_GOAL_PROGRESS => ModulesService::MODULE_NAME_GOALS,
        Setting::DASHBOARD_WIDGET_GOAL_PAYMENTS => ModulesService::MODULE_NAME_GOALS,
        Setting::DASHBOARD_WIDGET_ISSUES        => ModulesService::MODULE_NAME_ISSUES,
        Setting::DASHBOARD_WIDGET_SCHEDULES     => ModulesService::MODULE_NAME_MY_SCHEDULES,
    ];

    public function __construct(
        private readonly SettingsLoader        $settingsLoader,
        private readonly LockedResourceService $lockedResourceService
    ) {
    }

    /**
     * @param string $widgetName
     *
     * @return bool
     * @throws JWTDecodeFailureException
     */
    public function canFetchData(string $widgetName): bool
    {
        $module = self::getModuleForWidgetName($widgetName);
        return $this->settingsLoader->isDashboardWidgetVisible($widgetName)
               && $this->lockedResourceService->isAllowedToAccessModule($module);
    }

    /**
     * @param string $widgetName
     *
     * @return string
     */
    public static function getModuleForWidgetName(string $widgetName): string
    {
        $module = self::WIDGET_TO_MODULE_NAME[$widgetName] ?? null;
        if (is_null($module)) {
            throw new LogicException("Module name wasn't found for widget name {$widgetName}");
        }

        return $module;
    }
}