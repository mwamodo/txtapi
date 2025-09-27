<?php

namespace App\Enums;

enum TextMessageDirection: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';
}
