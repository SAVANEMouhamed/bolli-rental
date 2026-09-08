<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum CallReason: string
{
    use EnumOptions;

    case Reservation = 'reservation';
    case Complaint = 'complaint';
    case Support = 'support';
    case Payment = 'payment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Reservation => 'Réservation',
            self::Complaint => 'Réclamation',
            self::Support => 'Support technique',
            self::Payment => 'Paiement',
            self::Other => 'Autre',
        };
    }
}
