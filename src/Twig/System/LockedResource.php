<?php


namespace App\Twig\System;


use App\Controller\Page\SettingsLockModuleController;
use App\Controller\System\LockedResourceController;
use App\Controller\Core\Application;
use App\Services\Session\UserRolesSessionService;
use Exception;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LockedResource extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var UserRolesSessionService $userRolesSessionService
     */
    private $userRolesSessionService;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private $lockedResourceController;

    /**
     * @var SettingsLockModuleController $settingsLockModuleController
     */
    private SettingsLockModuleController $settingsLockModuleController;

    public function __construct(
        Application                     $app,
        AuthorizationCheckerInterface   $authorizationChecker,
        UserRolesSessionService         $userRolesSessionService,
        LockedResourceController        $lockedResourceController,
        SettingsLockModuleController    $settingsLockModuleController
    ) {
        $this->settingsLockModuleController = $settingsLockModuleController;
        $this->lockedResourceController     = $lockedResourceController;
        $this->userRolesSessionService      = $userRolesSessionService;
        $this->authorizationChecker         = $authorizationChecker;
        $this->app                          = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getClassForLockedResource', [$this, 'getClassForLockedResource']),
            new TwigFunction('isResourceLocked', [$this, 'isResourceLocked']),
            new TwigFunction('isAllowedToSeeResource', [$this, 'isAllowedToSeeResource']),
            new TwigFunction('isSystemLocked', [$this, 'isSystemLocked']),
            new TwigFunction('isModuleLocked', [$this, 'isModuleLocked']),
        ];
    }

    /**
     * This function must exists in twig as this is used for overall top-bar
     * @param string $record
     * @param string $type
     * @param string $target
     * @return string
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getClassForLockedResource(string $record, string $type, string $target): string
    {
        $isAllowedToSeeResourceStmt = $this->app->repositories->lockedResourceRepository->buildIsLockForRecordTypeAndTargetStatement();
        $isResourceLocked           = $this->app->repositories->lockedResourceRepository->executeIsLockForRecordTypeAndTargetStatement($isAllowedToSeeResourceStmt, $record, $type, $target);

        if( empty($isResourceLocked) ){
            return "text-success";
        }
            return "text-danger";
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     * @throws Exception
     */
    public function isResourceLocked(string $record, string $type, string $target): bool
    {
        return $this->lockedResourceController->isResourceLocked($record, $type, $target);
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     * @throws Exception
     */
    public function isAllowedToSeeResource(string $record, string $type, string $target): bool
    {
        return $this->lockedResourceController->isAllowedToSeeResource($record, $type, $target, false);
    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return $this->lockedResourceController->isSystemLocked();
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleLocked(string $moduleName): bool
    {
        return $this->settingsLockModuleController->isModuleLocked($moduleName);
    }

}