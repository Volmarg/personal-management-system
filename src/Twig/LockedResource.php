<?php


namespace App\Twig;


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

    public function __construct(Application $app, AuthorizationCheckerInterface $authorizationChecker, Security $security, UserRolesSessionService $userRolesSessionService) {
        $this->userRolesSessionService = $userRolesSessionService;
        $this->authorizationChecker    = $authorizationChecker;
        $this->app                     = $app;
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
        $locked_resource = $this->app->repositories->lockedResourceRepository->findOne($record, $type, $target);

        if( empty($locked_resource) ){
            return false;
        }
        return true;
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     */
    public function isResourceVisible(string $record, string $type, string $target): bool
    {
        $is_resource_locked      = $this->isResourceLocked($record, $type, $target);
        $can_see_locked_resource = $this->userRolesSessionService->hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        if(
                !$is_resource_locked
            ||  (
                    $is_resource_locked && $can_see_locked_resource
                )
        ){
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return $this->userRolesSessionService->hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);
    }

}