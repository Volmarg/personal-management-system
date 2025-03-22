<?php


namespace App\Action\System;


use App\Attribute\JwtAuthenticationDisabledAttribute;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\System\SecurityController;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\Security\JwtAuthenticationService;
use App\Services\Security\PasswordHashingService;
use App\Services\Storage\RequestSessionStorage;
use App\Services\TypeProcessor\ArrayHandler;
use App\Services\Validation\DtoValidatorService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAction extends AbstractController
{
    const KEY_SYSTEM_LOCK_PASSWORD    = 'systemLockPassword';

    /**
     * For loading twig templates
     */
    const MENU_NODE_NAME_MY_FILES     = 'my-files';
    const MENU_NODE_NAME_MY_IMAGES    = 'my-images';
    const MENU_NODE_NAME_MY_VIDEO     = 'my-video';

    const MENU_NODES_UPLOAD_BASED_MODULES_URL_PARTIALS = [
        self::MENU_NODE_NAME_MY_FILES,
        self::MENU_NODE_NAME_MY_IMAGES,
        self::MENU_NODE_NAME_MY_VIDEO,
    ];

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var DtoValidatorService $dtoValidator
     */
    private DtoValidatorService $dtoValidator;

    public function __construct(
        Application                               $app,
        Controllers                               $controllers,
        DtoValidatorService                       $dtoValidator,
        private readonly JwtAuthenticationService $jwtAuthenticationService,
        private readonly EntityManagerInterface   $em,
        private readonly PasswordHashingService   $passwordHashingService,
        private readonly TranslatorInterface      $translator,
    )
    {
        $this->app                      = $app;
        $this->controllers              = $controllers;
        $this->dtoValidator             = $dtoValidator;
    }

    /**
     * This method will either:
     * - set system in unlock state where locked resources are accessible,
     * - set system in lock state where locked resources are hidden,
     *
     * @Route("/toggle-resources-lock", name="system-toggle-resources-lock", methods="POST")
     * @param Request $request
     * @param SecurityController $securityController
     * @return JsonResponse
     * @throws Exception
     */
    public function toggleResourcesLock(Request $request, SecurityController $securityController): Response
    {
        RequestSessionStorage::$IS_TOGGLE_LOCK_CALL = true;

        $dataArray = RequestService::tryFromJsonBody($request);
        $password  = ArrayHandler::get($dataArray, 'password');

        $user = $this->jwtAuthenticationService->getUserFromRequest();
        if (!$this->jwtAuthenticationService->isSystemLocked()) {
            RequestSessionStorage::$IS_SYSTEM_LOCKED = true;
            $message = $this->app->translator->translate("security.lockResource.wholeSystemWasLocked");
            return BaseResponse::buildOkResponse($message)->toJsonResponse();
        }

        $userPassword    = $user->getLockPassword();
        $isPasswordValid = $securityController->isPasswordValid($user, $userPassword, $password);
        if (!$isPasswordValid) {
            $message = $this->app->translator->translate('security.lockResource.invalidPassword');
            return BaseResponse::buildBadRequestErrorResponse($message)->toJsonResponse();
        }

        RequestSessionStorage::$IS_SYSTEM_LOCKED = false;

        $message = $this->app->translator->translate("security.lockResource.wholeSystemHasBeenUnlocked");
        return BaseResponse::buildOkResponse($message)->toJsonResponse();
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
     * Handles registering user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/register-user", name: 'register_user', methods: [Request::METHOD_POST, Request::METHOD_OPTIONS])]
    #[JwtAuthenticationDisabledAttribute]
    public function registerUser(Request $request): JsonResponse
    {
        $dataArray             = RequestService::tryFromJsonBody($request);
        $email                 = ArrayHandler::get($dataArray, 'email');
        $username              = ArrayHandler::get($dataArray, 'username');
        $password              = ArrayHandler::get($dataArray, 'password');
        $passwordConfirmed     = ArrayHandler::get($dataArray, 'passwordConfirmed');
        $lockPassword          = ArrayHandler::get($dataArray, 'lockPassword');
        $lockPasswordConfirmed = ArrayHandler::get($dataArray, 'lockPasswordConfirmed');

        if ($password !== $passwordConfirmed) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('security.user.register.msg.passwordMismatch'))->toJsonResponse();
        }

        if ($lockPassword !== $lockPasswordConfirmed) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('security.user.register.msg.lockPasswordMismatch'))->toJsonResponse();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('security.user.register.msg.emailSyntaxInvalid'))->toJsonResponse();
        }

        $userRepo = $this->em->getRepository(User::class);
        if (!is_null($userRepo->findOneActive())) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('security.user.register.msg.activeUserExists'))->toJsonResponse();
        }

        // doesn't matter if deleted or not
        $userForEmail = $userRepo->findOneByEmail($email);
        if (!empty($userForEmail)) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('security.user.register.msg.userWithEmailExists'))->toJsonResponse();
        }

        $hashedPassword     = $this->passwordHashingService->encode($password);
        $hashedLockPassword = $this->passwordHashingService->encode($lockPassword);

        $user = new User();
        $user->setPassword($hashedPassword);
        $user->setLockPassword($hashedLockPassword);
        $user->setEnabled(true);
        $user->setUsername($username);
        $user->setUsernameCanonical($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);

        $this->em->persist($user);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @Route("/login", name="login")
     * This route is used by the {@see LexikJWTAuthenticationBundle}
     *
     * @return JsonResponse
     */
    #[JwtAuthenticationDisabledAttribute]
    public function login(): JsonResponse
    {
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Checks if registration is enabled
     *
     * @return JsonResponse
     */
    #[Route("/can-register", name: 'can_register_check', methods: [Request::METHOD_GET, Request::METHOD_OPTIONS])]
    #[JwtAuthenticationDisabledAttribute]
    public function hasAnyActiveUser(): JsonResponse
    {
        $userRepo = $this->em->getRepository(User::class);
        if (!is_null($userRepo->findOneActive())) {
            $msg = $this->translator->trans('security.user.register.msg.activeUserExists');
            return BaseResponse::buildNotFoundResponse($msg)->toJsonResponse();
        }

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}