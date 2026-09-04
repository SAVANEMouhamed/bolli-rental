<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Étiquettes de référence du service client. Liste fermée et stable : ce ne sont
     * pas des données de démonstration, l'application s'appuie dessus.
     *
     * @var list<string>
     */
    private const TAGS = [
        'urgent',
        'paiement',
        'annulation',
        'mobile money',
        'retard',
        'litige',
        'aéroport',
        'rappel client',
    ];

    public function run(): void
    {
        foreach (self::TAGS as $name) {
            Tag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
