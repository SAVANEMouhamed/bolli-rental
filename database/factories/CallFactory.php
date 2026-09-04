<?php

namespace Database\Factories;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Call>
 */
class CallFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            // Le rattachement à une réservation reste l'exception, comme en production.
            'reservation_id' => null,
            'direction' => fake()->randomElement(CallDirection::cases()),
            'reason' => fake()->randomElement(CallReason::cases()),
            'status' => fake()->randomElement(CallStatus::cases()),
            // Le standard tourne de 7 h à 21 h : sans cette borne, les courbes
            // horaires du tableau de bord seraient uniformes et sans intérêt.
            'called_at' => fake()->dateTimeBetween('-8 weeks', 'now')->setTime(
                fake()->numberBetween(7, 20),
                fake()->numberBetween(0, 59),
            ),
            'duration_seconds' => fake()->numberBetween(30, 1_800),
            'notes' => fake()->boolean(70) ? fake()->paragraph() : null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CallStatus::Resolved,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CallStatus::Pending,
        ]);
    }

    public function escalated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CallStatus::Escalated,
        ]);
    }

    /**
     * Un state paramétré plutôt que cinq méthodes identiques, une par motif.
     */
    public function reason(CallReason $reason): static
    {
        return $this->state(fn (array $attributes): array => [
            'reason' => $reason,
        ]);
    }

    /**
     * Rattache l'appel à une réservation, et donc au client de cette réservation :
     * un appel ne peut pas parler de la location de quelqu'un d'autre.
     */
    public function forReservation(Reservation $reservation): static
    {
        return $this->state(fn (array $attributes): array => [
            'reservation_id' => $reservation->id,
            'client_id' => $reservation->client_id,
        ]);
    }
}
