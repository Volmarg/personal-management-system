<?php
namespace App\Controller\Core;


use App\Controller\Utils\Utils;
use App\Services\Core\Logger;
use App\Services\Core\Translator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class Application extends AbstractController {

    /**
     * @var Repositories
     */
    public $repositories;

    /**
     * @var Forms
     */
    public $forms;

    /**
     * @var EntityManagerInterface
     */
    public $em;

    /**
     * @var \App\Services\Core\Translator $translator
     */
    public $translator;

    /**
     * @var LoggerInterface $logger
     */
    public $logger;

    /**
     * @var Logger $customLoggers
     */
    public $customLoggers;

    /**
     * @var Settings
     */
    public $settings;

    /**
     * @deprecated should no longer be used, needs to be checked if there is some old service to be removed / replaced
     */
    public $translations;

    /**
     * @var ConfigLoaders $configLoaders
     */
    public $configLoaders;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        Repositories            $repositories,
        Forms                   $forms,
        EntityManagerInterface  $em,
        LoggerInterface         $logger,
        Settings                $settings,
        Logger                  $customLoggers,
        TranslatorInterface     $translator,
        ConfigLoaders           $configLoaders,
        TokenStorageInterface   $tokenStorage
    ) {
        $this->customLoggers = $customLoggers;
        $this->repositories  = $repositories;
        $this->settings      = $settings;
        $this->logger        = $logger;
        $this->forms         = $forms;
        $this->em            = $em;
        $this->translator    = new Translator($translator);
        $this->configLoaders = $configLoaders;
        $this->tokenStorage  = $tokenStorage;
    }

    /**
     * Adds green box message on front
     * @param $message
     */
    public function addSuccessFlash($message)
    {
        $this->addFlash(Utils::FLASH_TYPE_SUCCESS, $message);
    }

    /**
     * Adds red box message on front
     * @param $message
     */
    public function addDangerFlash($message)
    {
        $this->addFlash(Utils::FLASH_TYPE_DANGER, $message);
    }

    /**
     * @param string $camelString
     * @return string
     */
    public static function camelCaseToSnakeCaseConverter(string $camelString)
    {
        $camelCaseToSnakeConverter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $snakeString               = $camelCaseToSnakeConverter->normalize($camelString);
        return $snakeString;
    }

    /**
     * @param string $snakeCase
     * @return string
     */
    public static function snakeCaseToCamelCaseConverter(string $snakeCase)
    {
        $camelCaseToSnakeConverter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $camelString               = $camelCaseToSnakeConverter->denormalize($snakeCase);
        return $camelString;
    }

    /**
     * Logs the standard exception data
     * @param Throwable $throwable
     * @param array $dataBag
     */
    public function logExceptionWasThrown(Throwable $throwable, array $dataBag = []): void
    {
        $message = $this->translator->translate('messages.general.internalServerError');

        $this->logger->critical($message, [
            "exceptionMessage" => $throwable->getMessage(),
            "exceptionCode"    => $throwable->getCode(),
            "exceptionTrace"   => $throwable->getTraceAsString(),
            "dataBag"          => $dataBag,
        ]);
    }

    /**
     * Returns currently logged in user
     * @return object|UserInterface|null
     */
    public function getCurrentlyLoggedInUser()
    {
        return $this->getUser();
    }

    /**
     * Will force logout from system
     */
    public function logoutCurrentlyLoggedInUser()
    {
        $this->tokenStorage->setToken(null);
    }

    /**
     * Begins a transaction
     */
    public function beginTransaction(): void
    {
        $this->em->beginTransaction();
    }

    /**
     * Commits the transaction
     */
    public function commitTransaction(): void
    {
        $this->em->commit();
    }

    /**
     * Rollback the transaction
     */
    public function rollbackTransaction(): void
    {
        $this->em->rollback();
    }
}