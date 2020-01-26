<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 20:59
 */

namespace App\Controller\Utils;


use App\Services\Translator;
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
     * @var Translator $translator
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
     * @var Translations $translations
     */
    public $translations;

    public function __construct(
        Repositories            $repositories,
        Forms                   $forms,
        EntityManagerInterface  $em,
        LoggerInterface         $logger,
        Settings                $settings,
        Translations            $translations
    ) {
        $this->repositories     = $repositories;
        $this->translations     = $translations;
        $this->settings         = $settings;
        $this->logger           = $logger;
        $this->forms            = $forms;
        $this->em               = $em;
        $this->translator       = new Translator();
    }

}