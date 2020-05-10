<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 20:59
 */

namespace App\Controller\Core;


use App\Controller\Core\Settings;
use App\Controller\Utils\Utils;
use App\Services\Core\Logger;
use App\Services\Core\Translator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var Logger $custom_loggers
     */
    public $custom_loggers;

    /**
     * @var Settings
     */
    public $settings;

    /**
     */
    public $translations;

    public function __construct(
        Repositories            $repositories,
        Forms                   $forms,
        EntityManagerInterface  $em,
        LoggerInterface         $logger,
        Settings                $settings,
        Logger                  $custom_loggers,
        TranslatorInterface     $translator
    ) {
        $this->custom_loggers   = $custom_loggers;
        $this->repositories     = $repositories;
        $this->settings         = $settings;
        $this->logger           = $logger;
        $this->forms            = $forms;
        $this->em               = $em;
        $this->translator       = new Translator($translator);
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
     * @param string $camel_string
     * @return string
     */
    public function camelCaseToSnakeCaseConverter(string $camel_string)
    {
        $camel_case_to_snake_converter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $snake_string                  = $camel_case_to_snake_converter->normalize($camel_string);
        return $snake_string;
    }

    /**
     * @param string $snake_case
     * @return string
     */
    public function snakeCaseToCamelCaseConverter(string $snake_case)
    {
        $camel_case_to_snake_converter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $camel_string                  = $camel_case_to_snake_converter->denormalize($snake_case);
        return $camel_string;
    }

}