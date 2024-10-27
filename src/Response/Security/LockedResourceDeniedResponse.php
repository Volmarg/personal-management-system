<?php

namespace App\Response\Security;

use App\Response\Base\BaseResponse;

class LockedResourceDeniedResponse extends BaseResponse
{
    public static function build(): self
    {
        return self::buildAccessDeniedResponse("This resource is locked!");
    }
}