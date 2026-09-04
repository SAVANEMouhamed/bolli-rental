<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_tag', function (Blueprint $table) {
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            // Pas de colonne d'identité : le couple est la clé, ce qui interdit
            // aussi de poser deux fois la même étiquette sur un appel.
            $table->primary(['call_id', 'tag_id']);

            // La clé primaire couvre déjà le sens call -> tags. Cet index sert le sens
            // inverse : filtrer les appels portant une étiquette donnée.
            $table->index(['tag_id', 'call_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_tag');
    }
};
