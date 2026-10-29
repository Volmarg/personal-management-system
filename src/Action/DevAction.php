<?php

namespace App\Action;

use App\Response\Base\BaseResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DevAction extends AbstractController
{
    public function debug(): JsonResponse
    {
        return BaseResponse::buildOkResponse("See " . self::class . "::" . __FUNCTION__)->toJsonResponse();
    }

}
