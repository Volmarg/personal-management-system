<?php


namespace App\Action\System;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use App\Controller\System\SecurityController;
use App\Controller\Utils\Utils;
use App\Entity\User;
use App\Form\User\UserRegisterType;
use App\Services\Exceptions\FormValidationException;
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

    const MENU_NODE_MODULE_NAME_ACHIEVEMENTS  = ModulesController::MODULE_NAME_ACHIEVEMENTS;
    const MENU_NODE_MODULE_NAME_GOALS         = ModulesController::MODULE_NAME_GOALS;
    const MENU_NODE_MODULE_NAME_MY_SCHEDULES  = ModulesController::MENU_NODE_MODULE_NAME_MY_SCHEDULES;
    const MENU_NODE_MODULE_NAME_MY_CONTACTS   = ModulesController::MODULE_NAME_CONTACTS;
    const MENU_NODE_MODULE_NAME_MY_FILES      = ModulesController::MODULE_NAME_FILES;
    const MENU_NODE_MODULE_NAME_MY_IMAGES     = ModulesController::MODULE_NAME_IMAGES;
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
    const MENU_NODE_NAME_MY_SCHEDULES = 'my-schedules';
    const MENU_NODE_NAME_MY_CONTACTS  = 'my-contacts';
    const MENU_NODE_NAME_MY_FILES     = 'my-files';
    const MENU_NODE_NAME_MY_IMAGES    = 'my-images';
    const MENU_NODE_NAME_MY_JOB       = 'my-job';
    const MENU_NODE_NAME_MY_PASSWORDS = 'my-passwords';
    const MENU_NODE_NAME_MY_PAYMENTS  = 'my-payments';
    const MENU_NODE_NAME_MY_SHOPPING  = 'my-shopping';
    const MENU_NODE_NAME_MY_TRAVELS   = 'my-travels';
    const MENU_NODE_NAME_NOTES        = 'notes';
    const MENU_NODE_NAME_REPORTS      = 'my-reports';
    const MENU_NODE_NAME_MY_ISSUES    = 'my-issues';

    const MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES = [
        self::MENU_NODE_MODULE_NAME_ACHIEVEMENTS  => 'MENU_NODE_MODULE_NAME_ACHIEVEMENTS',
        self::MENU_NODE_MODULE_NAME_GOALS         => 'MENU_NODE_MODULE_NAME_GOALS',
        self::MENU_NODE_MODULE_NAME_MY_SCHEDULES  => 'MENU_NODE_MODULE_NAME_MY_SCHEDULES',
        self::MENU_NODE_MODULE_NAME_MY_CONTACTS   => 'MENU_NODE_MODULE_NAME_MY_CONTACTS',
        self::MENU_NODE_MODULE_NAME_MY_FILES      => 'MENU_NODE_MODULE_NAME_MY_FILES',
        self::MENU_NODE_MODULE_NAME_MY_IMAGES     => 'MENU_NODE_MODULE_NAME_MY_IMAGES',
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
    private $app;

    /**
     * @var ExpirableSessionsService $expirable_sessions_service
     */
    private $expirable_sessions_service;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, ExpirableSessionsService $sessions_service, Controllers $controllers) {
        $this->app                        = $app;
        $this->controllers                = $controllers;
        $this->expirable_sessions_service = $sessions_service;
    }

    /**
     * @Route("/", name="app_default")
     * This is also main redirect when user logs in
     */
    public function index()
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/actions/render-menu-node-template", name="render_menu_node_template")
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function renderMenuNodeTemplate(Request $request)
    {
        try{
            $message = $this->app->translator->translate('responses.menu.nodeHasBeenRendered');

            if ( !$request->request->has(static::KEY_MENU_NODE_MODULE_NAME) ) {
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MENU_NODE_MODULE_NAME;
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $menu_node_module_name = $request->request->get(static::KEY_MENU_NODE_MODULE_NAME);

            if ( !array_key_exists($menu_node_module_name, static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP) ) {
                $message = $this->app->translator->translate('responses.menu.menuNodeWithNameWasNotFound') . $menu_node_module_name;
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $tpl_data = [
                static::KEY_CURR_URL => $request->server->get('HTTP_REFERER'),
                static::MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES[$menu_node_module_name] => $menu_node_module_name // because of constants used in tpl
            ];

            $template = $this->render(static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP[$menu_node_module_name], $tpl_data)->getContent();
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);

            $ajaxResponse = new AjaxResponse();
            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $json_response = $ajaxResponse->buildJsonResponse();
            return $json_response;
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
    public function admin(string $pageName)
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
     * @param SecurityController $security_controller
     * @return JsonResponse
     * @throws Exception
     */
    public function toggleResourcesLock(Request $request, SecurityController $security_controller): Response
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

        $is_unlocked_on_front = Utils::getBoolRepresentationOfBoolString($request->request->get(self::KEY_SYSTEM_LOCK_IS_UNLOCKED));
        $code                 = Response::HTTP_OK;

        try{

            if( $is_unlocked_on_front ){

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
                $user              = $this->getUser();
                $user_password     = $user->getLockPassword();
                $password          = $request->request->get(self::KEY_SYSTEM_LOCK_PASSWORD);
                $is_password_valid = $security_controller->isPasswordValid($user, $user_password, $password);

                if( !$is_password_valid ){
                    $message = $this->app->translator->translate('responses.lockResource.invalidPassword');
                    $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_UNAUTHORIZED, $message);
                    return $response;
                }

                UserRolesSessionService::addRolesToSession([User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES]);

                $system_lock_session_lifetime = $this->app->config_loaders->getConfigLoaderSession()->getSystemLockLifetime();
                $this->expirable_sessions_service->addSessionLifetime(ExpirableSessionsService::KEY_SESSION_SYSTEM_LOCK_LIFETIME, $system_lock_session_lifetime, [User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES]);

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
     * @param Request            $request
     * @param SecurityController $security_controller
     * @return JsonResponse
     * 
     */
    public function systemLockCreatePassword(Request $request, SecurityController $security_controller): Response
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
            $user            = $this->getUser();
            $has_password    = $user->hasLockPassword();

            $security_dto    = $security_controller->hashPassword($password);
            $hashed_password = $security_dto->getHashedPassword();

            $user->setLockPassword($hashed_password);
            $this->controllers->getUserController()->saveUser($user);

            if( $has_password ){
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
        $bad_request_message = $this->app->translator->translate("messages.ajax.failure.badRequest");

        if ( !$request->request->has(self::KEY_PATH_NAME) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_PATH_NAME]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $bad_request_message);
        }

        $path_name = $request->request->get(self::KEY_PATH_NAME);

        $is_exception  = false;
        $ajax_response = new AjaxResponse();
        $ajax_response->setCode(Response::HTTP_OK);

        try{
            $url = $this->generateUrl($path_name);
        }catch(Exception $e){
            $is_exception = true;
        }

        if( empty($url) || $is_exception ){
            $this->app->logger->critical('No route with this name was found', [$path_name]);
            $ajax_response->setMessage($bad_request_message);
            $ajax_response->setCode(Response::HTTP_BAD_REQUEST);

            return $ajax_response->buildJsonResponse();
        }

        $ajax_response->setRouteUrl($url);

        return $ajax_response->buildJsonResponse();
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
        $bad_request_message = $this->app->translator->translate("messages.ajax.failure.badRequest");

        if ( !$request->request->has(self::KEY_CONSTANT_NAME) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_CONSTANT_NAME]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $bad_request_message);
        }

        if ( !$request->request->has(self::KEY_NAMESPACE) ) {
            $this->app->logger->critical("Missing parameter in request", [self::KEY_NAMESPACE]);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $bad_request_message);
        }

        $namespace = $request->request->get(self::KEY_NAMESPACE);
        $constant  = $request->request->get(self::KEY_CONSTANT_NAME);

        $is_exception  = false;
        $ajax_response = new AjaxResponse();
        $ajax_response->setCode(Response::HTTP_OK);

        try{
            $constant_value = constant("{$namespace}::{$constant}");
        }catch(Exception $e){
            $is_exception = true;
        }

        if( empty($constant_value) || $is_exception ){
            $this->app->logger->critical("No such constant exists for given namespace", [
                self::KEY_CONSTANT_NAME => $constant,
                self::KEY_NAMESPACE     => $namespace,
            ]);
            $ajax_response->setMessage($bad_request_message);
            $ajax_response->setCode(Response::HTTP_BAD_REQUEST);

            return $ajax_response->buildJsonResponse();
        }

        $ajax_response->setConstantValue($constant_value);

        return $ajax_response->buildJsonResponse();
    }

    /**
     * The login page
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authentication_utils
     * @return Response
     */
    public function login(AuthenticationUtils $authentication_utils)
    {
        $error          = $authentication_utils->getLastAuthenticationError();
        $error_message  = "";

        $all_users      = $this->controllers->getUserController()->getAllUsers();
        $count_of_users = count($all_users);

        $show_register_button = true;
        if( empty($count_of_users) ){
            $show_register_button = false;
        }

        if(
                empty($error)
            &&  empty($count_of_users)
        ){
            $error_message = $this->app->translator->translate('login.errors.noExistingUserWasFoundPleaseContinueWithRegistration');
        }elseif(!empty($error)){
            $error_message = $this->app->translator->translate($error->getMessage(), $error->getMessageData(), 'security');
        }

        $template      = "security/pages/login.html.twig";
        $template_data = [
            'error_message'         => $error_message,
            'show_register_button'  => $show_register_button
        ];

        return $this->render($template, $template_data);
    }

    /**
     * User registration page
     * @Route("/register", name="register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        if( !$this->controllers->getSecurityController()->canRegisterUser() ){
            $message = $this->app->translator->translate('register.messages.notAllowedToRegisterAdditionalUsers');
            $this->app->addDangerFlash($message);
            return $this->redirectToRoute('login');
        }

        $all_users                  = $this->controllers->getUserController()->getAllUsers();
        $count_of_users             = count($all_users);

        $allow_to_register = true;
        if( !empty($count_of_users) ){
            $allow_to_register = false;
        }

        $user_register_form      = $this->app->forms->userRegisterForm();
        $user_register_form_view = $user_register_form->createView();

        // happens only on form submission
        if( $request->isXmlHttpRequest() )
        {
            $form_validation_violations = [];
            $code                       = Response::HTTP_OK;
            $success                    = true;
            $route_url                  = $this->generateUrl('login');
            $message                    = $this->app->translator->translate("");

            try{
                $user_register_form->handleRequest($request);

                if(
                        $user_register_form->isSubmitted()
                    &&  $user_register_form->isValid()
                )
                {
                    /**
                     * @var User $user_entity
                     */
                    $user_entity = $user_register_form->getData();

                    $raw_login_password     = $user_entity->getPassword();
                    $raw_lock_password      = $user_entity->getLockPassword();
                    $crypted_login_password = $this->controllers->getSecurityController()->hashPassword($raw_login_password)->getHashedPassword();
                    $crypted_lock_password  = $this->controllers->getSecurityController()->hashPassword($raw_lock_password)->getHashedPassword();

                    $user_entity->setRoles([User::ROLE_SUPER_ADMIN]);
                    $user_entity->setPassword($crypted_login_password);
                    $user_entity->setLockPassword($crypted_lock_password);;
                    $user_entity->setUsernameCanonical($user_entity->getUsername());
                    $user_entity->setEmailCanonical($user_entity->getEmail());

                    $this->controllers->getUserController()->saveUser($user_entity);
                }

            }catch(FormValidationException $exception){
                $form_validation_violations = $exception->getFormValidationViolations(true);
                $success                    = false;
                $code                       = Response::HTTP_BAD_REQUEST;
                $route_url                  = "";
                $message                    = $this->app->translator->translate('validators.messages.invalidDataHasBeenProvided');

                $this->app->logger->error("Some of the UserRegistration form inputs are invalid", $form_validation_violations);
            }catch(Exception | TypeError $e){
                $this->app->logger->critical("Exception was thrown while registering new user", [
                    "message" => $e->getMessage(),
                    "trace"   => $e->getTraceAsString(),
                ]);
                $success = false;
                $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = $this->app->translator->translate('messages.general.internalServerError');
            }

            $ajax_response = new AjaxResponse();
            $ajax_response->setInvalidFormFields($form_validation_violations);
            $ajax_response->setMessage($message);
            $ajax_response->setCode($code);
            $ajax_response->setSuccess($success);;
            $ajax_response->setValidatedFormPrefix(UserRegisterType::getFormPrefix());
            $ajax_response->setRouteUrl($route_url);

            return $ajax_response->buildJsonResponse();
        }

        $template      = "security/pages/register.html.twig";
        $template_data = [
            'allow_to_register'  => $allow_to_register,
            'user_register_form' => $user_register_form_view,
        ];

        return $this->render($template, $template_data);
    }

    /**
     * Main page when user is not logged int
     * @Route("/", name="home")
     */
    public function home()
    {
        // todo: register to dashboard when user is logged in
    }
}