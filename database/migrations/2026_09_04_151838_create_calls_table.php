<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            // Un appel garde toujours son client et son agent : on ne casse pas
            // l'historique du service client en supprimant une fiche.
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // Le rattachement à une réservation est facultatif (cahier des charges §2c).
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();

            // Valeurs contraintes côté application par des enums PHP castés.
            $table->string('direction');
            $table->string('reason');
            $table->string('status');

            $table->timestampTz('called_at');
            $table->unsignedInteger('duration_seconds');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Volume par jour/semaine et tri par défaut de la liste des appels.
            $table->index('called_at');
            // Filtre « agent + période » et classement des agents par volume traité.
            $table->index(['user_id', 'called_at']);
            // Filtre « statut + période » et répartition par statut du tableau de bord.
            $table->index(['status', 'called_at']);
            // Filtre « motif + période » et répartition par motif du tableau de bord.
            $table->index(['reason', 'called_at']);
            // Appels d'un client, appels d'une réservation (PostgreSQL n'indexe pas
            // seul les colonnes portantes de clés étrangères).
            $table->index('client_id');
            $table->index('reservation_id');
        });

        // Une durée d'appel négative n'a pas de sens ; PostgreSQL n'a pas d'entier
        // non signé, la contrainte remplace le type.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE calls ADD CONSTRAINT calls_duration_seconds_check CHECK (duration_seconds >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
