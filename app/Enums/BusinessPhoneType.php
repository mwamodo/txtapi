<?php

namespace App\Enums;

enum BusinessPhoneType: string
{
    case LOCAL = 'Local';
    case TOLL_FREE = 'TollFree';
    case MOBILE = 'Mobile';
}
