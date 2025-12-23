<?php

namespace App\Services\Security;

use App\Enum\UserModuleRightEnum;
use App\Services\Module\ModulesService;
use App\Services\Settings\SettingsLockModuleService;
use App\Services\System\LockedResourceService;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;

class JwtUserRightsHandler
{
    private readonly array $moduleAccessRightMap;

    public function __construct(
        private readonly SettingsLockModuleService $settingsLockModuleService,
        private readonly LockedResourceService     $lockedResourceService,
        private readonly LoggerInterface           $logger,
    ) {
        $this->moduleAccessRightMap = [
            ModulesService::MODULE_NAME_GOALS        => UserModuleRightEnum::CAN_ACCESS_GOALS_MODULE->name,
            ModulesService::MODULE_NAME_TODO         => UserModuleRightEnum::CAN_ACCESS_TODO_MODULE->name,
            ModulesService::MODULE_NAME_NOTES        => UserModuleRightEnum::CAN_ACCESS_NOTES_MODULE->name,
            ModulesService::MODULE_NAME_CONTACTS     => UserModuleRightEnum::CAN_ACCESS_CONTACTS_MODULE->name,
            ModulesService::MODULE_NAME_PASSWORDS    => UserModuleRightEnum::CAN_ACCESS_PASSWORDS_MODULE->name,
            ModulesService::MODULE_NAME_ACHIEVEMENTS => UserModuleRightEnum::CAN_ACCESS_ACHIEVEMENTS_MODULE->name,
            ModulesService::MODULE_NAME_MY_SCHEDULES => UserModuleRightEnum::CAN_ACCESS_CALENDAR_MODULE->name,
            ModulesService::MODULE_NAME_ISSUES       => UserModuleRightEnum::CAN_ACCESS_ISSUES_MODULE->name,
            ModulesService::MODULE_NAME_TRAVELS      => UserModuleRightEnum::CAN_ACCESS_TRAVELS_MODULE->name,
            ModulesService::MODULE_NAME_PAYMENTS     => UserModuleRightEnum::CAN_ACCESS_PAYMENTS_MODULE->name,
            ModulesService::MODULE_NAME_SHOPPING     => UserModuleRightEnum::CAN_ACCESS_SHOPPING_MODULE->name,
            ModulesService::MODULE_NAME_JOB          => UserModuleRightEnum::CAN_ACCESS_JOB_MODULE->name,
            ModulesService::MODULE_NAME_REPORTS      => UserModuleRightEnum::CAN_ACCESS_REPORTS_MODULE->name,
            ModulesService::MODULE_NAME_FILES        => UserModuleRightEnum::CAN_ACCESS_FILES_MODULE->name,
            ModulesService::MODULE_NAME_IMAGES       => UserModuleRightEnum::CAN_ACCESS_IMAGES_MODULE->name,
            ModulesService::MODULE_NAME_VIDEO        => UserModuleRightEnum::CAN_ACCESS_VIDEOS_MODULE->name,
        ];
    }

    /**
     * @return array[]
     * @throws JWTDecodeFailureException
     */
    public function getUserRights(): array
    {
        return [
            ...$this->getModuleRights(),
        ];
    }

    /**
     * @return array
     * @throws JWTDecodeFailureException
     */
    private function getModuleRights(): array
    {
        $granted = [];
        $this->settingsLockModuleService->refreshSettingsModuleLockDtos();
        $lockSettings = $this->settingsLockModuleService->getSettingsModuleLockDtos();
        foreach ($lockSettings as $lockSetting) {
            if ($lockSetting->isLocked() && $this->lockedResourceService->isSystemLocked()) {
                continue;
            }

            $right = $this->moduleAccessRightMap[$lockSetting->getName()] ?? null;
            if (!$right) {
                $this->logger->warning("No right mapped for module name: {$lockSetting->getName()}. Permission will not be granted!");
                continue;
            }

            $granted[] = $right;
        }

        return $granted;
    }
}