<?php

namespace App\Action;

use App\Response\Base\BaseResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DevAction extends AbstractController
{
    public function __construct()
    {
    }

    public function debug(): JsonResponse
    {
        return BaseResponse::buildOkResponse("See " . __CLASS__ . "::" . __FUNCTION__)->toJsonResponse();
    }

}
