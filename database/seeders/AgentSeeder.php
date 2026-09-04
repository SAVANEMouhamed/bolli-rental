<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $demo = User::query()->firstWhere('email', self::DEMO_EMAIL)
            ?? User::factory()->create([
                'name' => 'Agent de démonstration',
                'email' => self::DEMO_EMAIL,
            ]);

        // Le mot de passe est réappliqué à chaque exécution : le compte est public,
        // et un visiteur qui le changerait condamnerait sinon l'accès de recette
        // publié dans le README.
        $demo->forceFill(['password' => Hash::make(self::DEMO_PASSWORD)])->save();

        foreach (self::AGENTS as $agent) {
            if (User::query()->where('email', $agent['email'])->exists()) {
                continue;
            }

            // Ces agents ne servent qu'à peupler le classement du tableau de bord ;
            // personne ne s'y connecte. La factory poserait le mot de passe partagé
            // des tests, inacceptable sur une application publiée.
            User::factory()->create([
                ...$agent,
                'password' => Hash::make(Str::password(32)),
            ]);
        }
    }
}
