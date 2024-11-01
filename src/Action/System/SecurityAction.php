<?php


namespace App\Action\System;


use App\Listeners\Response\JwtTokenResponseListener;
use App\Response\Base\BaseResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/security", name: "security")]
class SecurityAction extends AbstractController {

    /**
     * Handles jwt token refresh. The content of this method is actually correct, because the jwt
     * refresh happens in {@see JwtTokenResponseListener}, so no need to do anything here.
     *
     * So why does this route exists? Because some url must be called for the listener to work.
     *
     * @return JsonResponse
     */
    #[Route("/jwt/refresh", name: "jwt.refresh")]
    public function refreshJwtToken(): JsonResponse
    {
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }
}