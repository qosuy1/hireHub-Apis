<?php

namespace App\Enums;

enum AvailabilityStatusEnum : string
{
    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';
    case BUSY = 'busy';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
