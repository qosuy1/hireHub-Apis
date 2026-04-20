<?php

namespace App\Enums;

enum UserTypeEnum : string
{
    case ADMIN = 'admin';
    case CLIENT = 'client';
    case FREELANCER = 'freelancer';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

