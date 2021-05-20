<?php

namespace App\Controller\Installer;

use App\Controller\Core\Application;
use ArrayIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Handler of installer logic
 */
class InstallerController extends AbstractController
{

    const STEP_1_WELCOME_PAGE              = "installerStep1WelcomePage";
    const STEP_2_CHECKING_SYSTEM           = "installerStep2CheckingSystem";
    const STEP_3_ENVIRONMENT_CONFIGURATION = "installerStep3EnvironmentConfiguration";
    const STEP_4_INSTALLATION_PROGRESS     = "installerStep4InstallationProgress";
    const STEP_5_INSTALLATION_STATUS       = "installerStep5InstallationStatus";

    const STEPS_ORDER = [
        self::STEP_1_WELCOME_PAGE,
        self::STEP_2_CHECKING_SYSTEM,
        self::STEP_3_ENVIRONMENT_CONFIGURATION,
        self::STEP_4_INSTALLATION_PROGRESS,
        self::STEP_5_INSTALLATION_STATUS,
    ];

    /**
     * @var SessionInterface $session
     */
    private SessionInterface $session;

    /**
     * @var Application $application
     */
    private Application $application;

    public function __construct(SessionInterface $session, Application $application)
    {
        $this->session = $session;
    }

    /**
     * Will return information if user can go on with installation or not,
     * - for example it should not be possible to continue if installation has been already finished
     *
     * @return bool
     */
    public function isAllowedToInstall(): bool
    {
        // add env variable for this and set it on the end to unlock installation - do not rely on session keys here
    }

    /**
     * Will save given step name in session
     *
     * @param string $stepName
     * @param $value
     * @throws Exception
     */
    public function saveStepStateInSession(string $stepName, $value): void
    {
        if( !is_scalar($value) ){
            throw new Exception("Only scalar values can be stored in session");
        }

        // check if step name is supported - or add just a method isStepSupported
    }

    /**
     * Will get current step state from session or null if nothing was found
     *
     * @param string $stepName
     * @return string|null
     */
    public function getStepStateFromSession(string $stepName): ?string
    {

    }

    /**
     * Will check if previous step toward current is done
     *
     * @param string $currentStepName
     * @return bool
     */
    public function getPreviousStepState(string $currentStepName): bool
    {
        $reversedOrder      = array_reverse(self::STEPS_ORDER); // is a must since ArrayIterator has no `back` method
        $searchedIndex      = array_search($currentStepName, $reversedOrder);

        $stepsOrderIterator = $this->getStepsOrderIterator();
        $stepsOrderIterator->seek($searchedIndex);
        $stepsOrderIterator->next(); // previous since order is reversed

        $previousStepName  = $stepsOrderIterator->current();
        $previousStepIndex = array_search($previousStepName, $reversedOrder);
        if( 0 === $previousStepIndex){
            return true; // it's the first state so it's ok
        }

        return $previousStepName;
    }

    public function goToPreviousStep(): Response //redirect
    {

    }

    public function goToNextStep(): Response //redirect
    {

    }

    private function getCurrentStepName(): string
    {

    }

    private function getPreviousStepName(): ?string
    {

    }

    private function getNextStepName(): ?string
    {

    }

    /**
     * Return the @see InstallerController::STEPS_ORDER
     * in form of @see ArrayIterator
     *
     * @return ArrayIterator
     */
    private function getStepsOrderIterator(): ArrayIterator
    {
        $reversedOrder      = array_reverse(self::STEPS_ORDER); // is a must since ArrayIterator has no `back` method
        $stepsOrderIterator = new ArrayIterator($reversedOrder);
        return $stepsOrderIterator;
    }

}