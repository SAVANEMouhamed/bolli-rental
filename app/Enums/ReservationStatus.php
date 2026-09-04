<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum ReservationStatus: string
{
    use EnumOptions;

    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Libellé affiché à l'agent. Centralisé ici pour que le français
     * ne soit pas dupliqué dans chaque composant Vue.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'En cours',
            self::Completed => 'Terminée',
            self::Cancelled => 'Annulée',
        };
    }
}
