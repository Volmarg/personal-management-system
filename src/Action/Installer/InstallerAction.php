<?php

namespace App\Action\Installer;

use App\Controller\Core\Application;
use App\Controller\Core\ConfigLoaders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Actions for handling the GUI based installation process
 * - not supporting ajax calls in this step, this is not needed,
 * - only production mode is supported in this case,
 *
 * Class InstallerAction
 * @Route("/installer", name="installer_")
 */
class InstallerAction extends AbstractController
{
    // todo: store each step in session to load it upon going back (clear the keys when installation is over)
    // todo: store keys such as INSTALLER_STEP_1_STATUS = false / true to prevent jumping between steps

    /**
     * @var Application $application
     */
    private Application $application;

    /**
     * @var ConfigLoaders $configLoaders
     */
    private ConfigLoaders $configLoaders;

    /**
     * InstallerAction constructor.
     * @param Application $application
     * @param ConfigLoaders $configLoaders
     */
    public function __construct(Application  $application, ConfigLoaders $configLoaders)
    {
        // move the hardcoded default values to ConfigLoaderEnvironment (all methods must be static there - Autoinstaller.php purpose.)
    }

    /**
     * @return Response
     * @Route("/step-1", name="installer_step_1")
     */
    public function displayWelcomePage(): Response
    {
        die('yay');
        // show short information - hello do You want to install..
    }

    /**
     * @return Response
     * @Route("/step-2", name="installer_step_2")
     */
    public function displayCheckingSystem(): Response
    {
        // do not block progress - only show warning if something fails,
        // check requirements, check what is missing etc.
    }

    /**
     * @return Response
     * @Route("/step-3", name="installer_step_3")
     */
    public function displayEnvironmentConfiguration(): Response
    {
        // show the form with few questions regarding the database configuration
        // env folders etc -> use default if nothing is provided (show as placeholder)
    }

    /**
     * @return Response
     * @Route("/step-4", name="installer_step_4")
     */
    public function displayInstallationInProgress(): Response
    {
        // show the page -> make sleep then add loader and tell to pls wait or anything like this
        // use all the all the calls present in Autoinstaller.php
    }

    /**
     * @return Response
     * @Route("/step-5", name="installer_step_5")
     */
    public function displayInstallationStatus(): Response
    {
        // error or success
    }

}