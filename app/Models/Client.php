<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 */
#[Fillable(['first_name', 'last_name', 'phone', 'email'])]
#[Appends(['full_name'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @return HasMany<Call, $this>
     */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    /**
     * Un client est toujours affiché par son nom complet : on le compose une fois
     * ici plutôt que dans chaque écran.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /**
     * Recherche d'un client au moment d'enregistrer un appel : l'agent tape un
     * bout de nom ou de numéro, sans savoir dans quel champ il tombe.
     *
     * `LOWER(...) LIKE ?` plutôt que `ILIKE` : PostgreSQL sert la production,
     * SQLite la suite de tests, et seul le premier connaît `ILIKE`.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->whereRaw(
            "LOWER(first_name || ' ' || last_name || ' ' || phone) LIKE ?",
            ['%'.mb_strtolower($term).'%'],
        );
    }
}
