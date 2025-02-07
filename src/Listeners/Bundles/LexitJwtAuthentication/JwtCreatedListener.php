<?php

namespace App\Listeners\Bundles\LexitJwtAuthentication;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\Files\PathService;
use App\Services\Security\JwtAuthenticationService;
use App\Services\Session\SessionsService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
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
        private readonly UserRepository $userRepository,
        private readonly SessionsService $sessionsService
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

        $userSessionData    = $this->sessionsService->getForUser($user->getId());
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
            JwtAuthenticationService::JWT_IS_SYSTEM_LOCKED => $userSessionData->isSystemLocked(),
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
}