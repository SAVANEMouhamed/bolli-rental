<?php

namespace App\Models;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use Database\Factories\CallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
}
