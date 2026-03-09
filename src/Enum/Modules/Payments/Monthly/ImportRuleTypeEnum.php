<?php

namespace App\Enum\Modules\Payments\Monthly;

enum ImportRuleTypeEnum: string
{
    case REGEX = 'regex';
    case EXACT = 'exact';
}
