<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'calls_count' => $this->whenCounted('calls'),
            'reservations_count' => $this->whenCounted('reservations'),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
        ];
    }
}
