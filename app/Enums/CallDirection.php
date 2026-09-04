<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum CallDirection: string
{
    use EnumOptions;

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
