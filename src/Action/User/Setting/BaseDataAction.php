<?php

namespace App\Action\User\Setting;

use App\Controller\Core\Env;
use App\Response\Base\BaseResponse;
use App\Services\Files\PathService;
use App\Services\RequestService;
use App\Services\Security\JwtAuthenticationService;
use App\Services\TypeProcessor\ArrayHandler;
use App\Services\Validation\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles user settings base data actions
 */
class BaseDataAction extends AbstractController
{
    public function __construct(
        private readonly ValidationService        $validationService,
        private readonly TranslatorInterface      $translator,
        private readonly EntityManagerInterface   $entityManager,
        private readonly JwtAuthenticationService $jwtAuthenticationService,
        private readonly LoggerInterface          $logger
    ){}

    /**
     * Saves the user personal data
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/user/base-data/personal-data/save", name: "user.base_data.personal_data.save", methods: [Request::METHOD_OPTIONS, Request::METHOD_POST])]
    public function savePersonalData(Request $request): JsonResponse
    {
        $json = $request->getContent();
        if (!$this->validationService->isJsonValid($json)) {
            return BaseResponse::buildInvalidJsonResponse()->toJsonResponse();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $username  = ArrayHandler::get($dataArray, 'username');

        $user = $this->jwtAuthenticationService->getUserFromRequest();
        $user->setUsername($username);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $msg = $this->translator->trans('user.settings.personalData.msg.updateSuccess');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * Changes the user profile image to the one that was recently uploaded.
     * This route is called directly after the file upload.
     *
     * It handles:
     * - removing file that is currently used for user profile image,
     * - setting new image as profile,
     *
     * @return JsonResponse
     */
    #[Route("/user/base-data/profile-image/change", name: "user.base_data.profile_image.change", methods: [Request::METHOD_OPTIONS, Request::METHOD_GET])]
    public function changeProfileImage(): JsonResponse
    {
        /** @var null | SplFileInfo $latestFile */
        $finder       = Finder::create();
        $latestFile   = null;
        $allFilesPath = [];
        foreach ($finder->files()->in(PathService::getProfileImageUploadDir()) as $file) {
            if (is_null($latestFile) || $latestFile->getCTime() < $file->getCTime()) {
                $latestFile = $file;
            }
            $allFilesPath[] = $file->getRealPath();
        }

        if (is_null($latestFile)) {
            $msg = $this->translator->trans('user.settings.personalData.msg.profileImage.noProfileImageWasFound');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $allFilesPath = array_filter($allFilesPath, fn(string $path) => $path != $latestFile->getRealPath());
        foreach ($allFilesPath as $filePath) {
            $isremoved = unlink($filePath);
            if (!$isremoved) {
                $this->logger->warning("Could not remove profile image: {$filePath}");
            }
        }

        $msg = $this->translator->trans('user.settings.personalData.msg.profileImage.updateSuccess');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * Changes the user email
     *
     * @param string $emailAddress
     *
     * @return JsonResponse
     */
    #[Route("/user/base-data/email/change/{emailAddress}", name: "user.base_data.email.change", methods: [Request::METHOD_OPTIONS, Request::METHOD_GET])]
    public function changeEmail(string $emailAddress): JsonResponse
    {
        if (Env::isDemo()) {
            return BaseResponse::buildBadRequestErrorResponse("You are not allowed to do that!")->toJsonResponse();
        }

        if (empty($emailAddress)) {
            $message = $this->translator->trans('user.settings.email.msg.emailEmpty');
            return BaseResponse::buildBadRequestErrorResponse($message)->toJsonResponse();
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $message = $this->translator->trans('user.settings.email.msg.emailSyntaxInvalid');
            return BaseResponse::buildBadRequestErrorResponse($message)->toJsonResponse();
        }

        $user = $this->jwtAuthenticationService->getUserFromRequest();
        $user->setEmail($emailAddress);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

}
