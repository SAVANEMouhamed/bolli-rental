<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Ordre imposé par les dépendances : étiquettes et agents avant les appels.
     */
    public function run(): void
    {
        $this->call([
            TagSeeder::class,
            AgentSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
