<?php

namespace App\Controller\System;

use App\Controller\Core\Application;
use App\Controller\Page\SettingsLockModuleController;
use App\Entity\System\LockedResource;
use App\Repository\System\LockedResourceRepository;
use App\Services\Security\JwtAuthenticationService;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class LockedResourceController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var SettingsLockModuleController $settingsLockModuleController
     */
    private SettingsLockModuleController $settingsLockModuleController;

    public function __construct(
        Application                               $app,
        SettingsLockModuleController              $settingsLockModuleController,
        private readonly JwtAuthenticationService $jwtAuthenticationService,
        private readonly LockedResourceRepository $lockedResourceRepository,
        private readonly EntityManagerInterface   $em,
        private readonly TranslatorInterface      $translator
    ) {
        $this->app                          = $app;
        $this->settingsLockModuleController = $settingsLockModuleController;
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @param Statement|null $stmt
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isResourceLocked(string $record, string $type, string $target, Statement $stmt = null): bool
    {
        if( is_null($stmt) ){
            $stmt = $this->lockedResourceRepository->buildIsLockForRecordTypeAndTargetStatement($type);
        }

        switch($type){
            case LockedResource::TYPE_ENTITY:
                $isLockedResource = $this->lockedResourceRepository->executeIsLockForRecordTypeAndTargetStatement($stmt, $record, $type, $target);
                return !empty($isLockedResource);

            // in case of directory we need to check every parent directory for lock
            // if any parent is locked then we also lock given directory
            case LockedResource::TYPE_DIRECTORY:

                $pattern = "#(.*)[\/]{1}(.*)#";
                while( preg_match($pattern, $record, $matches) ){ # walk over the path and build parent path

                    $lockedResource = $this->lockedResourceRepository->executeIsLockForRecordTypeAndTargetStatement($stmt, $record, $type, $target);
                    if( !empty($lockedResource) ){
                        return true;
                    }

                    if( !array_key_exists(2, $matches) ){
                        return false;
                    }
                    $replace = DIRECTORY_SEPARATOR . preg_quote($matches[2]);
                    $record  = preg_replace("#{$replace}#", "", $record);

                }

                return false;

            case LockedResource::TYPE_MODULE:
                /**
                 * Module lock is handled via @see SettingsLockModuleController, but this check was added due to usage of
                 * @see LockedResourceController::isAllowedToSeeResource()
                 */
                return false;


            default:
                throw new Exception("This locked resource type is not supported");
        }

    }

    /**
     * @description either create or remove the lock
     *
     * @param string $record
     * @param string $type
     * @param string $target
     *
     * @return bool
     */
    public function toggleLock(string $record, string $type, string $target): bool
    {
        $entity = $this->em->getRepository(LockedResource::class)->findOneEntity($record, $type, $target);
        if (!empty($entity)) {
            $this->em->remove($entity);
            $this->em->flush();

            return false;
        }

        $lockedResource = new LockedResource();
        $lockedResource->setRecord($record);
        $lockedResource->setType($type);
        $lockedResource->setTarget($target);

        $this->em->persist($lockedResource);
        $this->em->flush();

        return true;
    }

    /**
     * @return bool
     *
     * @throws JWTDecodeFailureException
     */
    public function isSystemLocked(): bool
    {
        return $this->jwtAuthenticationService->isSystemLocked();
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @param bool $showFlashMessage
     * @param Statement|null $stmt
     * @return bool
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isAllowedToSeeResource(string $record, string $type, string $target, bool $showFlashMessage = true, Statement $stmt = null): bool
    {
        $isResourceLocked = $this->isResourceLocked($record, $type, $target, $stmt);
        $isSystemLocked   = $this->isSystemLocked();
        $isModuleLocked   = $this->settingsLockModuleController->isModuleLocked($target);

        if(
                ( $isResourceLocked && $isSystemLocked )
            ||  ( $isModuleLocked   && $isSystemLocked )
        ){
            if($showFlashMessage){
                $message = $this->translator->trans("security.lockResource.youAreNotAllowedToSeeThisResource");
                $this->app->addDangerFlash($message);
            }
            return false;
        }

        return true;
    }

    /**
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LockedResource $lockedResource): void
    {
        $this->lockedResourceRepository->remove($lockedResource);
    }

    /**
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LockedResource $lockedResource): void
    {
        $this->lockedResourceRepository->add($lockedResource);
    }

}
