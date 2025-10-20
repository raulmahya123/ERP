<?php

namespace App\Enums;

enum PicaStatus: string
{
    case OPEN        = 'open';
    case EFFECTIVE   = 'effective';
    case INEFFECTIVE = 'ineffective';
    case CLOSED      = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
