<?php

namespace App\Action\User\Setting;

use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\Security\JwtAuthenticationService;
use App\Services\Security\PasswordHashingService;
use App\Services\System\EnvReader;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles user settings security actions
 */
class SecurityAction extends AbstractController
{
    public function __construct(
        private readonly PasswordHashingService   $passwordHashingService,
        private readonly TranslatorInterface      $translator,
        private readonly EntityManagerInterface   $entityManager,
        private readonly JwtAuthenticationService $jwtAuthenticationService,
    ){}

    /**
     * Changes the user password
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/user/security/password/change", name: "user.security.password.change", methods: [Request::METHOD_OPTIONS, Request::METHOD_POST])]
    public function changePassword(Request $request): JsonResponse
    {
        if (EnvReader::isDemo()) {
            return BaseResponse::buildBadRequestErrorResponse("You are not allowed to do that!")->toJsonResponse();
        }

        $dataArray         = RequestService::tryFromJsonBody($request);
        $password          = ArrayHandler::get($dataArray, 'password');
        $passwordConfirmed = ArrayHandler::get($dataArray, 'passwordConfirmed');

        if ($password !== $passwordConfirmed) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('user.settings.password.passwordMismatch'))->toJsonResponse();
        }

        $hashedPassword = $this->passwordHashingService->encode($password);

        $user = $this->jwtAuthenticationService->getUserFromRequest();
        $user->setPassword($hashedPassword);

        $this->entityManager->flush($user);

        $msg = $this->translator->trans('user.settings.password.updateSuccess');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * Changes the user password
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/user/security/lock-password/change", name: "user.security.lock_password.change", methods: [Request::METHOD_OPTIONS, Request::METHOD_POST])]
    public function changeLockPassword(Request $request): JsonResponse
    {
        if (EnvReader::isDemo()) {
            return BaseResponse::buildBadRequestErrorResponse("You are not allowed to do that!")->toJsonResponse();
        }

        $dataArray         = RequestService::tryFromJsonBody($request);
        $password          = ArrayHandler::get($dataArray, 'password');
        $passwordConfirmed = ArrayHandler::get($dataArray, 'passwordConfirmed');

        if ($password !== $passwordConfirmed) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('user.settings.password.passwordMismatch'))->toJsonResponse();
        }

        $hashedPassword = $this->passwordHashingService->encode($password);

        $user = $this->jwtAuthenticationService->getUserFromRequest();
        $user->setLockPassword($hashedPassword);

        $this->entityManager->flush($user);

        $msg = $this->translator->trans('user.settings.password.updateSuccess');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

}
