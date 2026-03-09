<?php

namespace App\Enum\Modules\Payments\Monthly;

enum ImportFieldEnum: string
{
    case DATE = 'date';
    case MONEY = 'money';
    case DESCRIPTION = 'description';
    case CURRENCY = 'currency';
}
