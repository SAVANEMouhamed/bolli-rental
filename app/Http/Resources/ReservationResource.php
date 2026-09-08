<?php

namespace App\Http\Resources;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reservation
 */
class ReservationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle' => $this->vehicle,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'client' => $this->whenLoaded(
                'client',
                fn (): ?ClientResource => $this->client === null
                    ? null
                    : new ClientResource($this->client),
            ),
            'calls_count' => $this->whenCounted('calls'),
        ];
    }
}
