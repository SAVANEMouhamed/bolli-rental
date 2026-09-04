<?php

namespace App\Enums;

enum CallStatus: string
{
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
