<?php


namespace App\Twig;


use App\Controller\System\LockedResourceController;
use App\Controller\Utils\Application;
use App\Entity\User;
use App\Services\Session\UserRolesSessionService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
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

    public function __construct(
        Application                     $app,
        AuthorizationCheckerInterface   $authorizationChecker,
        Security                        $security,
        UserRolesSessionService         $userRolesSessionService,
        LockedResourceController        $lockedResourceController
    ) {
        $this->lockedResourceController = $lockedResourceController;
        $this->userRolesSessionService  = $userRolesSessionService;
        $this->authorizationChecker     = $authorizationChecker;
        $this->app                      = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getClassForLockedResource', [$this, 'getClassForLockedResource']),
            new TwigFunction('isResourceLocked', [$this, 'isResourceLocked']),
            new TwigFunction('isResourceVisible', [$this, 'isResourceVisible']),
            new TwigFunction('isSystemLocked', [$this, 'isSystemLocked']),
        ];
    }

    /**
     * This function must exists in twig as this is used for overall top-bar
     * @param string $record
     * @param string $type
     * @param string $target
     * @return mixed[]
     */
    public function getClassForLockedResource(string $record, string $type, string $target): string
    {
        $locked_resource = $this->app->repositories->lockedResourceRepository->findOne($record, $type, $target);

        if( empty($locked_resource) ){
            return "text-success";
        }
            return "text-danger";
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
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
     */
    public function isResourceVisible(string $record, string $type, string $target): bool
    {
        return $this->lockedResourceController->isResourceVisible($record, $type, $target);
    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return $this->lockedResourceController->isSystemLocked();
    }

}