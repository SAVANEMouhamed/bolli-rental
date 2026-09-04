<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Identifiants du compte de recette, documentés dans le README : l'inscription
     * publique est désactivée, c'est la seule porte d'entrée de la démonstration.
     */
    public const DEMO_EMAIL = 'demo@bollirental.africa';

    public const DEMO_PASSWORD = 'Bolli@Demo2026!';

    /**
     * Les autres agents du plateau, nécessaires au classement par volume traité.
     *
     * @var list<array{name: string, email: string}>
     */
    private const AGENTS = [
        ['name' => 'Aïcha Bamba', 'email' => 'aicha.bamba@bollirental.africa'],
        ['name' => 'Serge Kouassi', 'email' => 'serge.kouassi@bollirental.africa'],
        ['name' => 'Fatou Diarra', 'email' => 'fatou.diarra@bollirental.africa'],
    ];

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Agent de démonstration',
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
        ]);

        foreach (self::AGENTS as $agent) {
            User::factory()->create($agent);
        }
    }
}
