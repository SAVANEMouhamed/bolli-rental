<?php

use App\Enums\ReservationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            // On ne supprime pas un client qui a un historique de location.
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('vehicle');
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->string('status')->default(ReservationStatus::Active->value);
            $table->timestamps();

            // PostgreSQL n'indexe pas automatiquement la colonne portante d'une clé
            // étrangère : index explicite pour lister les réservations d'un client.
            $table->index('client_id');
        });

        // Intégrité au niveau base : une location ne peut pas finir avant de commencer.
        // SQLite (base de test) ne sait pas ajouter une contrainte après création.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE reservations ADD CONSTRAINT reservations_dates_check CHECK (ends_at > starts_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
