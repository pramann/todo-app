<?php

namespace App\Enum;

use App\Trait\EnumTrait;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}