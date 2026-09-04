<?php

namespace Database\Seeders;

use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    private const CLIENTS = 16;

    private const CALLS = 320;

    /**
     * Répartitions volontairement déséquilibrées : un plateau réel résout la plupart
     * de ses appels et escalade rarement. Une distribution uniforme donnerait un
     * tableau de bord plat, incapable de montrer que les agrégations fonctionnent.
     *
     * @var array<string, int>
     */
    private const STATUS_WEIGHTS = [
        CallStatus::Resolved->value => 65,
        CallStatus::Pending->value => 25,
        CallStatus::Escalated->value => 10,
    ];

    /**
     * @var array<string, int>
     */
    private const REASON_WEIGHTS = [
        CallReason::Reservation->value => 35,
        CallReason::Payment->value => 22,
        CallReason::Complaint->value => 18,
        CallReason::Support->value => 15,
        CallReason::Other->value => 10,
    ];

    public function run(): void
    {
        $agents = User::query()->get();
        $tags = Tag::query()->get();

        $clients = Client::factory()->count(self::CLIENTS)->create();
        $reservations = $this->seedReservations($clients);

        $statuses = $this->weighted(self::STATUS_WEIGHTS);
        $reasons = $this->weighted(self::REASON_WEIGHTS);

        // Factory::state() relie la closure à la factory : elle ne peut donc pas
        // appeler une méthode du seeder via $this, tout passe par les captures.
        $state = function () use ($clients, $reservations, $agents, $statuses, $reasons): array {
            // Environ un appel sur trois concerne une location identifiée ; il hérite
            // alors du client de cette réservation.
            $reservation = fake()->boolean(30) ? $reservations->random() : null;

            return [
                'client_id' => $reservation?->client_id ?? $clients->random()->id,
                'reservation_id' => $reservation?->id,
                'user_id' => $agents->random()->id,
                'status' => CallStatus::from(fake()->randomElement($statuses)),
                'reason' => CallReason::from(fake()->randomElement($reasons)),
            ];
        };

        $calls = Call::factory()->count(self::CALLS)->state($state)->create();

        $this->attachTags($calls, $tags);
    }

    /**
     * 26 réservations réparties sur les trois statuts, dont quelques clients
     * fidèles avec plusieurs locations.
     *
     * @param  Collection<int, Client>  $clients
     * @return Collection<int, Reservation>
     */
    private function seedReservations(Collection $clients): Collection
    {
        $forClient = fn (): array => ['client_id' => $clients->random()->id];

        return Reservation::factory()->count(14)->completed()->state($forClient)->create()
            ->concat(Reservation::factory()->count(8)->active()->state($forClient)->create())
            ->concat(Reservation::factory()->count(4)->cancelled()->state($forClient)->create());
    }

    /**
     * Déplie une table de poids en liste tirable au sort.
     *
     * @param  array<string, int>  $weights
     * @return list<string>
     */
    private function weighted(array $weights): array
    {
        $pool = [];

        foreach ($weights as $value => $weight) {
            $pool = array_merge($pool, array_fill(0, $weight, $value));
        }

        return $pool;
    }

    /**
     * @param  Collection<int, Call>  $calls
     * @param  Collection<int, Tag>  $tags
     */
    private function attachTags(Collection $calls, Collection $tags): void
    {
        $calls->each(function (Call $call) use ($tags): void {
            $picked = $tags->random(fake()->numberBetween(0, 2));

            if ($picked->isNotEmpty()) {
                $call->tags()->attach($picked->pluck('id'));
            }
        });
    }
}
