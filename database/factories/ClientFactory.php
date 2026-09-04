<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Faker n'a pas de locale ivoirienne : ces listes évitent une démo peuplée
     * de clients français, peu crédible pour une agence d'Abidjan.
     *
     * @var list<string>
     */
    private const FIRST_NAMES = [
        'Aya', 'Koffi', 'Adjoua', 'Yao', 'Affoué', 'Kouadio', 'Akissi', 'Kouassi',
        'Mariam', 'Ibrahim', 'Fatoumata', 'Souleymane', 'Aminata', 'Seydou',
        'Grace', 'Emmanuel', 'Sarah', 'Jean-Marc', 'Nadège', 'Olivier',
    ];

    /**
     * @var list<string>
     */
    private const LAST_NAMES = [
        'Kouamé', 'Traoré', 'Konan', 'Diabaté', 'Yao', 'Bamba', 'N\'Guessan',
        'Ouattara', 'Coulibaly', 'Gnahoré', 'Assi', 'Touré', 'Kone', 'Brou',
        'Adou', 'Zadi', 'Djédjé', 'Sangaré',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->randomElement(self::FIRST_NAMES);
        $lastName = fake()->randomElement(self::LAST_NAMES);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            // Format ivoirien depuis 2021 : 10 chiffres après l'indicatif +225.
            'phone' => '+225 '.fake()->unique()->numerify('0# ## ## ## ##'),
            'email' => fake()->boolean(70)
                ? Str::lower(Str::ascii($firstName).'.'.Str::ascii(str_replace([' ', "'"], '', $lastName))).fake()->unique()->numberBetween(1, 999).'@example.ci'
                : null,
        ];
    }
}
