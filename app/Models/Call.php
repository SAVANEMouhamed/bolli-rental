<?php

namespace App\Models;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use Carbon\CarbonImmutable;
use Database\Factories\CallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property int|null $reservation_id
 * @property CallDirection $direction
 * @property CallReason $reason
 * @property CallStatus $status
 * @property Carbon $called_at
 * @property int $duration_seconds
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'client_id',
    'user_id',
    'reservation_id',
    'direction',
    'reason',
    'status',
    'called_at',
    'duration_seconds',
    'notes',
])]
class Call extends Model
{
    /** @use HasFactory<CallFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => CallDirection::class,
            'reason' => CallReason::class,
            'status' => CallStatus::class,
            'called_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * L'agent du service client qui a traité l'appel. La colonne reste `user_id`
     * (convention Laravel), la relation porte le nom du métier.
     *
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Facultative : un appel n'est pas toujours lié à une réservation existante.
     *
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Filtres de la liste des appels. Vit dans le modèle parce que l'écran web et
     * l'API REST appliquent exactement les mêmes règles.
     *
     * Les valeurs arrivent déjà validées d'une Form Request : les enums et
     * l'identifiant d'agent sont contraints, rien n'est interpolé dans le SQL.
     *
     * @param  Builder<$this>  $query
     * @param  array<string, mixed>  $filters
     */
    #[Scope]
    protected function filtered(Builder $query, array $filters): void
    {
        $query
            ->when($filters['agent'] ?? null, fn (Builder $q, int|string $agent) => $q->where('user_id', $agent))
            ->when($filters['client'] ?? null, fn (Builder $q, int|string $client) => $q->where('client_id', $client))
            ->when($filters['reservation'] ?? null, fn (Builder $q, int|string $reservation) => $q->where('reservation_id', $reservation))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['reason'] ?? null, fn (Builder $q, string $reason) => $q->where('reason', $reason))
            ->when($filters['direction'] ?? null, fn (Builder $q, string $direction) => $q->where('direction', $direction))
            ->when($filters['tag'] ?? null, fn (Builder $q, string $tag) => $q->whereHas('tags', fn (Builder $t) => $t->where('slug', $tag)))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->where('called_at', '>=', CarbonImmutable::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->where('called_at', '<=', CarbonImmutable::parse($to)->endOfDay()))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $group) => $this->applySearch($group, $search),
            ));
    }

    /**
     * `LOWER(...) LIKE ?` plutôt que `ILIKE` : PostgreSQL sert la production,
     * SQLite sert la suite de tests, et seul le premier connaît `ILIKE`.
     *
     * @param  Builder<$this>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $term = '%'.mb_strtolower($search).'%';

        $query
            ->whereHas('client', fn (Builder $client) => $client
                ->whereRaw('LOWER(first_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(phone) LIKE ?', [$term]))
            ->orWhereRaw('LOWER(notes) LIKE ?', [$term]);
    }
}
