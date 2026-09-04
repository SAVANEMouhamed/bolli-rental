<?php

namespace App\Enums;

enum CallReason: string
{
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
