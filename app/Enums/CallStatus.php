<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum CallStatus: string
{
    use EnumOptions;

    case Resolved = 'resolved';
    case Pending = 'pending';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::Resolved => 'Résolu',
            self::Pending => 'En attente',
            self::Escalated => 'Escaladé',
        };
    }
}
