<?php

declare(strict_types=1);

use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\AgentSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * Le seeder est rejoué à chaque déploiement de la démonstration : il doit être
 * rejouable sans dupliquer les données ni casser l'accès publié dans le README.
 */
function seededCounts(): array
{
    return [
        'agents' => User::query()->count(),
        'clients' => Client::query()->count(),
        'reservations' => Reservation::query()->count(),
        'calls' => Call::query()->count(),
    ];
}

it('rejoue les seeders sans dupliquer le jeu de démonstration', function (): void {
    $this->seed();
    $first = seededCounts();

    $this->seed();

    expect(seededCounts())->toBe($first)
        ->and($first['agents'])->toBe(4);
});

it('remet le mot de passe du compte de démonstration à la valeur documentée', function (): void {
    $this->seed();

    User::query()->where('email', AgentSeeder::DEMO_EMAIL)
        ->update(['password' => Hash::make('mot-de-passe-change-par-un-visiteur')]);

    $this->seed();

    $this->post(route('login.store'), [
        'email' => AgentSeeder::DEMO_EMAIL,
        'password' => AgentSeeder::DEMO_PASSWORD,
    ])->assertRedirect(route('dashboard'));
});

it('ne laisse aucun mot de passe partagé sur les agents de démonstration', function (): void {
    $this->seed();

    $agents = User::query()->where('email', '!=', AgentSeeder::DEMO_EMAIL)->get();

    expect($agents)->toHaveCount(3);

    foreach ($agents as $agent) {
        expect(Hash::check('password', $agent->password))->toBeFalse();
    }
});
