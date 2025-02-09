<?php

namespace App\Listeners\Bundles\LexitJwtAuthentication;

use App\Controller\System\LockedResourceController;
use App\Entity\User;
use App\Services\Files\PathService;
use App\Services\Security\JwtAuthenticationService;
use App\Services\Storage\RequestSessionStorage;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

/**
 * Handles the action when jwt has been created -> manipulates the payload / adds new fields
 * @link https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/2-data-customization.md
 */
class JwtCreatedListener implements EventSubscriberInterface
{

    public const JWT_KEY_USER_ID = "userId";
    private const JWT_KEY_NICKNAME = "nickname";
    private const JWT_KEY_PROFILE_PIC_PATH = "profilePicturePath";

    public function __construct(
        private readonly JwtAuthenticationService $jwtAuthenticationService,
        private readonly LockedResourceController $lockedResourceController
    ){}

    /**
     * Handle the event
     *
     * @param JWTCreatedEvent $event
     */
    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        $data = $event->getData();

        $profilePicturePath = '/dummy-user.png';
        $isUpload = false;
        foreach (Finder::create()->files()->in(PathService::getProfileImageUploadDir()) as $file) {
            $profilePicturePath = $file->getRealPath();
            $isUpload = true;
            break;
        }


        $newData = array_merge($data, [
            JwtAuthenticationService::JWT_KEY_EMAIL        => $user->getEmail(),
            JwtAuthenticationService::JWT_KEY_USERNAME     => $user->getUsername(),
            JwtAuthenticationService::JWT_IS_SYSTEM_LOCKED => $this->isSystemLocked(),
            self::JWT_KEY_USER_ID                      => $user->getId(),
            self::JWT_KEY_NICKNAME                     => $user->getNickname(),
            self::JWT_KEY_PROFILE_PIC_PATH             => PathService::getPublicPath($profilePicturePath, $isUpload),
        ]);

        $event->setData($newData);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => "onJwtCreated",
        ];
    }

    /**
     * Returns the system lock state:
     * - if it's toggle lock request then it reads the state that has been set,
     * - for other requests previous lock state will be reused,
     *
     * @return bool
     * @throws JWTDecodeFailureException
     */
    private function isSystemLocked(): bool
    {
        if (RequestSessionStorage::$IS_TOGGLE_LOCK_CALL) {
            return RequestSessionStorage::$IS_SYSTEM_LOCKED;
        }

        $jwtToken = $this->jwtAuthenticationService->extractJwtFromRequest();
        if (empty($jwtToken)) {
            return true;
        }

        return $this->lockedResourceController->isSystemLocked();
    }
}