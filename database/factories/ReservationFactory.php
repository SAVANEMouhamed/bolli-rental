<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Flotte annoncée par Bolli Rental : berlines, SUV, vans.
     *
     * @var list<string>
     */
    private const VEHICLES = [
        'Toyota Corolla', 'Hyundai Elantra', 'Kia Cerato', 'Peugeot 301',
        'Toyota RAV4', 'Hyundai Tucson', 'Kia Sportage', 'Nissan X-Trail',
        'Toyota Hiace', 'Renault Trafic', 'Mercedes Vito',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-8 weeks', '+2 weeks');

        return [
            'client_id' => Client::factory(),
            'vehicle' => fake()->randomElement(self::VEHICLES),
            'starts_at' => $startsAt,
            // La contrainte base exige ends_at > starts_at.
            'ends_at' => (clone $startsAt)->modify('+'.fake()->numberBetween(1, 14).' days'),
            'status' => ReservationStatus::Active,
        ];
    }

    /**
     * Location en cours : commencée, pas encore rendue.
     */
    public function active(): static
    {
        $startsAt = fake()->dateTimeBetween('-10 days', '-1 day');

        return $this->state(fn (array $attributes): array => [
            'status' => ReservationStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => fake()->dateTimeBetween('+1 day', '+10 days'),
        ]);
    }

    /**
     * Location terminée : entièrement dans le passé.
     */
    public function completed(): static
    {
        $startsAt = fake()->dateTimeBetween('-8 weeks', '-3 weeks');

        return $this->state(fn (array $attributes): array => [
            'status' => ReservationStatus::Completed,
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+'.fake()->numberBetween(1, 10).' days'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReservationStatus::Cancelled,
        ]);
    }
}
