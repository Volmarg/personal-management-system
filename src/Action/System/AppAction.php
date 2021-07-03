<?php


namespace App\Action\System;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use App\Controller\System\SecurityController;
use App\Controller\Utils\Utils;
use App\Services\Validation\DtoValidatorService;
use App\DTO\User\UserRegistrationDTO;
use App\Entity\User;
use App\Form\User\UserRegisterType;
use App\Services\Session\ExpirableSessionsService;
use App\Services\Session\UserRolesSessionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use TypeError;

class AppAction extends AbstractController {
    const TWIG_MENU_NODE_PATH = 'page-elements/components/sidebar/menu-nodes/';
    const TWIG_EXT            = DOT.'twig';

    const KEY_MENU_NODE_MODULE_NAME = 'menu_node_module_name';

    const KEY_CURR_URL                = 'currUrl';
    const KEY_SYSTEM_LOCK_PASSWORD    = 'systemLockPassword';
    const KEY_SYSTEM_LOCK_IS_UNLOCKED = 'isUnlocked';
    const KEY_PATH_NAME               = 'pathName';
    const KEY_CONSTANT_NAME           = 'constantName';
    const KEY_NAMESPACE               = 'namespace';

    // todo: strange mapping here, could use the module names directly, but then why reporting is not a module, same with schedules?
    const MENU_NODE_MODULE_NAME_ACHIEVEMENTS  = ModulesController::MODULE_NAME_ACHIEVEMENTS;
    const MENU_NODE_MODULE_NAME_GOALS         = ModulesController::MODULE_NAME_GOALS;
    const MENU_NODE_MODULE_NAME_TODO          = ModulesController::MODULE_NAME_TODO;
    const MENU_NODE_MODULE_NAME_MY_SCHEDULES  = ModulesController::MODULE_NAME_MY_SCHEDULES;
    const MENU_NODE_MODULE_NAME_MY_CONTACTS   = ModulesController::MODULE_NAME_CONTACTS;
    const MENU_NODE_MODULE_NAME_MY_FILES      = ModulesController::MODULE_NAME_FILES;
    const MENU_NODE_MODULE_NAME_MY_IMAGES     = ModulesController::MODULE_NAME_IMAGES;
    const MENU_NODE_MODULE_NAME_MY_VIDEO      = ModulesController::MODULE_NAME_VIDEO;
    const MENU_NODE_MODULE_NAME_MY_JOB        = ModulesController::MODULE_NAME_JOB;
    const MENU_NODE_MODULE_NAME_MY_PASSWORDS  = ModulesController::MODULE_NAME_PASSWORDS;
    const MENU_NODE_MODULE_NAME_MY_PAYMENTS   = ModulesController::MODULE_NAME_PAYMENTS;
    const MENU_NODE_MODULE_NAME_MY_SHOPPING   = ModulesController::MODULE_NAME_SHOPPING;
    const MENU_NODE_MODULE_NAME_MY_TRAVELS    = ModulesController::MODULE_NAME_TRAVELS;
    const MENU_NODE_MODULE_NAME_NOTES         = ModulesController::MODULE_NAME_NOTES;
    const MENU_NODE_MODULE_NAME_MY_ISSUES     = ModulesController::MODULE_NAME_ISSUES;
    const MENU_NODE_MODULE_NAME_REPORTS       = ModulesController::MENU_NODE_MODULE_NAME_REPORTS;

    /**
     * For loading twig templates
     */
    const MENU_NODE_NAME_ACHIEVEMENTS = 'achievements';
    const MENU_NODE_NAME_GOALS        = 'goals';
    const MENU_NODE_NAME_TODO         = 'todo';
    const MENU_NODE_NAME_MY_SCHEDULES = 'my-schedules';
    const MENU_NODE_NAME_MY_CONTACTS  = 'my-contacts';
    const MENU_NODE_NAME_MY_FILES     = 'my-files';
    const MENU_NODE_NAME_MY_IMAGES    = 'my-images';
    const MENU_NODE_NAME_MY_VIDEO     = 'my-video';
    const MENU_NODE_NAME_MY_JOB       = 'my-job';
    const MENU_NODE_NAME_MY_PASSWORDS = 'my-passwords';
    const MENU_NODE_NAME_MY_PAYMENTS  = 'my-payments';
    const MENU_NODE_NAME_MY_SHOPPING  = 'my-shopping';
    const MENU_NODE_NAME_MY_TRAVELS   = 'my-travels';
    const MENU_NODE_NAME_NOTES        = 'notes';
    const MENU_NODE_NAME_REPORTS      = 'my-reports';
    const MENU_NODE_NAME_MY_ISSUES    = 'my-issues';

    const MENU_NODES_UPLOAD_BASED_MODULES_URL_PARTIALS = [
        self::MENU_NODE_NAME_MY_FILES,
        self::MENU_NODE_NAME_MY_IMAGES,
        self::MENU_NODE_NAME_MY_VIDEO,
    ];

    const MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES = [
        self::MENU_NODE_MODULE_NAME_ACHIEVEMENTS  => 'MENU_NODE_MODULE_NAME_ACHIEVEMENTS',
        self::MENU_NODE_MODULE_NAME_GOALS         => 'MENU_NODE_MODULE_NAME_GOALS',
        self::MENU_NODE_MODULE_NAME_MY_SCHEDULES  => 'MENU_NODE_MODULE_NAME_MY_SCHEDULES',
        self::MENU_NODE_MODULE_NAME_MY_CONTACTS   => 'MENU_NODE_MODULE_NAME_MY_CONTACTS',
        self::MENU_NODE_MODULE_NAME_MY_FILES      => 'MENU_NODE_MODULE_NAME_MY_FILES',
        self::MENU_NODE_MODULE_NAME_MY_IMAGES     => 'MENU_NODE_MODULE_NAME_MY_IMAGES',
        self::MENU_NODE_MODULE_NAME_MY_VIDEO      => 'MENU_NODE_MODULE_NAME_MY_VIDEO',
        self::MENU_NODE_MODULE_NAME_MY_JOB        => 'MENU_NODE_MODULE_NAME_MY_JOB',
        self::MENU_NODE_MODULE_NAME_MY_PASSWORDS  => 'MENU_NODE_MODULE_NAME_MY_PASSWORDS',
        self::MENU_NODE_MODULE_NAME_MY_PAYMENTS   => 'MENU_NODE_MODULE_NAME_MY_PAYMENTS',
        self::MENU_NODE_MODULE_NAME_MY_SHOPPING   => 'MENU_NODE_MODULE_NAME_MY_SHOPPING',
        self::MENU_NODE_MODULE_NAME_MY_TRAVELS    => 'MENU_NODE_MODULE_NAME_MY_TRAVELS',
        self::MENU_NODE_MODULE_NAME_NOTES         => 'MENU_NODE_MODULE_NAME_NOTES',
        self::MENU_NODE_MODULE_NAME_REPORTS       => 'MENU_NODE_MODULE_NAME_REPORTS',
        self::MENU_NODE_NAME_MY_ISSUES            => 'MENU_NODE_NAME_MY_ISSUES',
    ];

    const MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP = [
        self::MENU_NODE_MODULE_NAME_ACHIEVEMENTS  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_ACHIEVEMENTS . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_GOALS         => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_GOALS        . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_SCHEDULES  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_SCHEDULES . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_CONTACTS   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_CONTACTS  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_FILES      => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_FILES     . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_IMAGES     => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_IMAGES    . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_VIDEO      => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_VIDEO     . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_JOB        => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_JOB       . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_PASSWORDS  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_PASSWORDS . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_PAYMENTS   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_PAYMENTS  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_SHOPPING   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_SHOPPING  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_TRAVELS    => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_TRAVELS   . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_NOTES         => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_NOTES        . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_REPORTS       => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_REPORTS      . self::TWIG_EXT,
        self::MENU_NODE_NAME_MY_ISSUES            => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_ISSUES    . self::TWIG_EXT,
    ];

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var ExpirableSessionsService $expirableSessionsService
     */
    private ExpirableSessionsService $expirableSessionsService;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var DtoValidatorService $dtoValidator
     */
    private DtoValidatorService $dtoValidator;

    public function __construct(Application $app, ExpirableSessionsService $sessions_service, Controllers $controllers, DtoValidatorService $dtoValidator)
    {
        $this->app                      = $app;
        $this->controllers              = $controllers;
        $this->dtoValidator             = $dtoValidator;
        $this->expirableSessionsService = $sessions_service;
    }

    /**
     * @Route("/", name="app_default")
     * This is also main redirect when user logs in
     */
    public function index(): Response
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/actions/render-menu-node-template", name="render_menu_node_template")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function renderMenuNodeTemplate(Request $request): Response
    {
        try{
            $message = $this->app->translator->translate('responses.menu.nodeHasBeenRendered');
            if ( !$request->request->has(static::KEY_MENU_NODE_MODULE_NAME) ) {
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MENU_NODE_MODULE_NAME;
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $menuNodeModuleName = $request->request->get(static::KEY_MENU_NODE_MODULE_NAME);
            if ( !array_key_exists($menuNodeModuleName, static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP) ) {
                $message = $this->app->translator->translate('responses.menu.menuNodeWithNameWasNotFound') . $menuNodeModuleName;
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $tplData = [
                static::KEY_CURR_URL => $request->server->get('HTTP_REFERER'),
                static::MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES[$menuNodeModuleName] => $menuNodeModuleName // because of constants used in tpl
            ];

            $template = $this->render(static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP[$menuNodeModuleName], $tplData)->getContent();
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);

            $ajaxResponse = new AjaxResponse();
            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $jsonResponse = $ajaxResponse->buildJsonResponse();
            return $jsonResponse;
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, $message, $template);

    }

    /**
     * This originally came with symfonator
     * @Route("admin/{pageName}", name="admin_default")
     * @deprecated - to be removed
     * @param string $pageName Page name
     * @return Response
     */
    public function admin(string $pageName): Response
    {
        return $this->render(
            sprintf(
                "%s.html.twig",
                $pageName
            )
        );
    }

    /**
     * This method will either:
     * - set system in unlock state where locked resources are accessible,
     * - set system in lock state where locked resources are hidden,
     * @Route("/api/system/toggle-resources-lock", name="system-toggle-resources-lock", methods="POST")
     * @param Request $request
     * @param SecurityController $securityController
     * @return JsonResponse
     * @throws Exception
     */
    public function toggleResourcesLock(Request $request, SecurityController $securityController): Response
    {
        if( !$request->isXmlHttpRequest() ){
            $message  = $this->app->translator->translate('responses.general.youAreNotAllowedToCallThisLogic');
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
            return $response;
        }

        if( !$request->request->has(self::KEY_SYSTEM_LOCK_PASSWORD) ){
            $message  = $this->app->translator->translate('responses.lockResource.passwordIsMissing');
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
            return $response;
        }

        if( !$request->request->has(self::KEY_SYSTEM_LOCK_IS_UNLOCKED) ){
            $message  = $this->app->translator->translate('responses.general.missingRequiredParameter'. self::KEY_SYSTEM_LOCK_IS_UNLOCKED);
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
            return $response;
        }

        $isUnlockedOnFront = Utils::getBoolRepresentationOfBoolString($request->request->get(self::KEY_SYSTEM_LOCK_IS_UNLOCKED));
        $code              = Response::HTTP_OK;

        try{

            if( $isUnlockedOnFront ){

                // the session has expired - force to reload gui
                if( !UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES) ) {
                    $message = $this->app->translator->translate("messages.lock.unlockExpiredReloadingPage");
                }else{
                    $message = $this->app->translator->translate("messages.lock.wholeSystemWasLocked");
                }

                UserRolesSessionService::removeRolesFromSession([User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES]);
            }else{

                /**
                 * @var User $user
                 */
                $user            = $this->getUser();
                $userPassword    = $user->getLockPassword();
                $password        = $request->request->get(self::KEY_SYSTEM_LOCK_PASSWORD);
                $isPasswordValid = $securityController->isPasswordValid($user, $userPassword, $password);

                if( !$isPasswordValid ){
                    $message = $this->app->translator->translate('responses.lockResource.invalidPassword');
                    $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
                    return $response;
                }

                UserRolesSessionService::addRolesToSession([User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES]);

                $systemLockSessionLifetime = $this->app->configLoaders->getConfigLoaderSession()->getSystemLockLifetime();
                $this->expirableSessionsService->addSessionLifetime(ExpirableSessionsService::KEY_SESSION_SYSTEM_LOCK_LIFETIME, $systemLockSessionLifetime, [User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES]);

                $message = $this->app->translator->translate("messages.lock.wholeSystemHasBeenUnlocked");

            }
        } catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $this->app->translator->translate("messages.lock.failedToToggleLockForWholeSystem");
            $this->app->logger->info($message, [
                "exceptionMessage"  => $e->getMessage(),
                "exceptionCode"     => $e->getCode(),
            ]);
        }

        $response = AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
        return $response;
    }

    /**
     * @Route("/api/system/system-lock-set-password", name="system-lock-create-password", methods="POST")
     * @param Request $request
     * @param SecurityController $securityController
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function systemLockCreatePassword(Request $request, SecurityController $securityController): Response
    {
        if( !$request->request->has(self::KEY_SYSTEM_LOCK_PASSWORD) ){
            $message = $this->app->translator->translate('responses.lockResource.passwordIsMissing');
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
            return $response;
        }

        $password = $request->request->get(self::KEY_SYSTEM_LOCK_PASSWORD);

        try{

            /**
             * @var User $user
             */
            $user           = $this->getUser();
            $hasPassword    = $user->hasLockPassword();

            $securityDto    = $securityController->hashPassword($password);
            $hashedPassword = $securityDto->getHashedPassword();

            $user->setLockPassword($hashedPassword);
            $this->controllers->getUserController()->saveUser($user);

            if( $hasPassword ){
                $message = $this->app->translator->translate('responses.lockResource.passwordHasBeenCreated');
            }else{
                $message = $this->app->translator->translate('responses.lockResource.passwordHasBeenUpdated');
            }

            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, $message);
        } catch(Exception $e){
            $message = $this->app->translator->translate("responses.lockResource.failedToSetLockPassword");
            $this->app->logger->info($message, [
                "exceptionMessage"  => $e->getMessage(),
                "exceptionCode"     => $e->getCode(),
            ]);

            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return $response;
    }

    /**
     * Will return ajax response with url for given route if such route was found, otherwise bad request is returned
     *
     * @Route("/api/system/get-url-for-path-name", name="get_url_for_path_name", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getUrlForPathName(Request $request): JsonResponse
    {
        $badRequestMessage = $this->app->translator->translate("messages.ajax.failure.badRequest");
        if ( !$request->request->has(self::KEY_PATH_NAME) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_PATH_NAME]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $badRequestMessage);
        }

        $pathName = $request->request->get(self::KEY_PATH_NAME);

        $isException  = false;
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setCode(Response::HTTP_OK);

        try{
            $url = $this->generateUrl($pathName);
        }catch(Exception $e){
            $isException = true;
        }

        if( empty($url) || $isException ){
            $this->app->logger->critical('No route with this name was found', [$pathName]);
            $ajaxResponse->setMessage($badRequestMessage);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);

            return $ajaxResponse->buildJsonResponse();
        }

        $ajaxResponse->setRouteUrl($url);
        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * Will return a value of given constant from given namespace back to frontend
     *
     * @Route("/api/system/get-constant-value-from-backend", name="get_constant_value_from_backend", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getConstantValueFromBackend(Request $request): JsonResponse
    {
        $badRequestMessage = $this->app->translator->translate("messages.ajax.failure.badRequest");
        if ( !$request->request->has(self::KEY_CONSTANT_NAME) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_CONSTANT_NAME]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $badRequestMessage);
        }

        if ( !$request->request->has(self::KEY_NAMESPACE) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_NAMESPACE]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $badRequestMessage);
        }

        $namespace = $request->request->get(self::KEY_NAMESPACE);
        $constant  = $request->request->get(self::KEY_CONSTANT_NAME);

        $isException  = false;
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setCode(Response::HTTP_OK);

        try{
            $constantValue = constant("{$namespace}::{$constant}");
        }catch(Exception $e){
            $isException = true;
        }

        if( empty($constantValue) || $isException ){
            $this->app->logger->critical("No such constant exists for given namespace", [
                self::KEY_CONSTANT_NAME => $constant,
                self::KEY_NAMESPACE     => $namespace,
            ]);
            $ajaxResponse->setMessage($badRequestMessage);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);

            return $ajaxResponse->buildJsonResponse();
        }

        $ajaxResponse->setConstantValue($constantValue);
        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * The login page
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @throws Exception
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error        = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = "";

        $allUsers     = $this->controllers->getUserController()->getAllUsers();
        $countOfUsers = count($allUsers);

        $showRegisterButton = true;
        if( empty($countOfUsers) ){
            $showRegisterButton = false;
        }

        if(
                empty($error)
            &&  empty($countOfUsers)
        ){
            $errorMessage = $this->app->translator->translate('login.errors.noExistingUserWasFoundPleaseContinueWithRegistration');
        }elseif(!empty($error)){
            $errorMessage = $this->app->translator->translate($error->getMessage(), $error->getMessageData(), 'security');
        }

        $template     = "security/pages/login.html.twig";
        $templateData = [
            'error_message'         => $errorMessage,
            'show_register_button'  => $showRegisterButton
        ];

        return $this->render($template, $templateData);
    }

    /**
     * User registration page
     * @Route("/register", name="register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        if( !$this->controllers->getSecurityController()->canRegisterUser() ){
            $message = $this->app->translator->translate('register.messages.notAllowedToRegisterAdditionalUsers');
            $this->app->addDangerFlash($message);
            return $this->redirectToRoute('login');
        }

        $validationResultVo = null;
        $allUsers           = $this->controllers->getUserController()->getAllUsers();
        $countOfUsers       = count($allUsers);

        $allowToRegister = true;
        if( !empty($countOfUsers) ){
            $allowToRegister = false;
        }

        $userRegisterForm     = $this->app->forms->userRegisterForm();
        $userRegisterFormView = $userRegisterForm->createView();

        // happens only on form submission
        if( $request->isXmlHttpRequest() )
        {
            $formValidationViolations = [];
            $code                     = Response::HTTP_OK;
            $success                  = true;
            $routeUrl                 = $this->generateUrl('login');
            $message                  = $this->app->translator->translate("");

            try{
                $userRegisterForm->handleRequest($request);

                if(
                        $userRegisterForm->isSubmitted()
                    &&  $userRegisterForm->isValid()
                )
                {

                    /**
                     * @var UserRegistrationDTO $userRegistrationDto
                     */
                    $userRegistrationDto = $userRegisterForm->getData();
                    $validationResultVo  = $this->dtoValidator->doValidate($userRegistrationDto);

                    if( $validationResultVo->isValid() ){
                        $userEntity          = new User();

                        $cryptedLoginPassword = $this->controllers->getSecurityController()->hashPassword($userRegistrationDto->getPassword())->getHashedPassword();
                        $cryptedLockPassword  = $this->controllers->getSecurityController()->hashPassword($userRegistrationDto->getLockPassword())->getHashedPassword();

                        $userEntity->setRoles([User::ROLE_SUPER_ADMIN]);
                        $userEntity->setPassword($cryptedLoginPassword);
                        $userEntity->setLockPassword($cryptedLockPassword);

                        $userEntity->setUsername($userRegistrationDto->getUsername());
                        $userEntity->setUsernameCanonical($userRegistrationDto->getUsername());

                        $userEntity->setEmail($userRegistrationDto->getEmail());
                        $userEntity->setEmailCanonical($userRegistrationDto->getEmail());

                        $this->controllers->getUserController()->saveUser($userEntity);
                    }
                }

            }catch(Exception | TypeError $e){
                $this->app->logger->critical("Exception was thrown while registering new user", [
                    "message" => $e->getMessage(),
                    "trace"   => $e->getTraceAsString(),
                ]);
                $success = false;
                $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = $this->app->translator->translate('messages.general.internalServerError');
            }

            $ajaxResponse = new AjaxResponse();
            if(
                    !is_null($validationResultVo)
                &&  !$validationResultVo->isValid()
            ){
                $formValidationViolations = $validationResultVo->getInvalidFieldsMessages();
                $success                  = false;
                $code                     = Response::HTTP_BAD_REQUEST;
                $routeUrl                 = "";
                $message                  = $this->app->translator->translate('validators.messages.invalidDataHasBeenProvided');

                $this->app->logger->error("Some of the UserRegistration form inputs are invalid", $formValidationViolations);
            }

            $ajaxResponse->setInvalidFormFields($formValidationViolations);
            $ajaxResponse->setMessage($message);
            $ajaxResponse->setCode($code);
            $ajaxResponse->setSuccess($success);;
            $ajaxResponse->setValidatedFormPrefix(UserRegisterType::getFormPrefix());
            $ajaxResponse->setRouteUrl($routeUrl);

            return $ajaxResponse->buildJsonResponse();
        }

        $template     = "security/pages/register.html.twig";
        $templateData = [
            'allow_to_register'  => $allowToRegister,
            'user_register_form' => $userRegisterFormView,
        ];

        return $this->render($template, $templateData);
    }

    /**
     * Main page when user is not logged int
     * @Route("/", name="home")
     */
    public function home()
    {
        // todo: register to dashboard when user is logged in
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        // nothing to be done here, required to use in path generator, but symfony auth. overrides this.
    }
}