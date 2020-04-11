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
use App\Services\Core\Translator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        Settings                $settings
    ) {
        $this->repositories     = $repositories;
        $this->settings         = $settings;
        $this->logger           = $logger;
        $this->forms            = $forms;
        $this->em               = $em;
        $this->translator       = new Translator();
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


}