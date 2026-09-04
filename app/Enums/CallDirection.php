<?php

namespace App\Enums;

enum CallDirection: string
{
    case Inbound = 'inbound';
    case Outbound = 'outbound';

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Entrant',
            self::Outbound => 'Sortant',
        };
    }
}
