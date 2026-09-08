<?php

namespace App\Http\Resources;

use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Call
 */
class CallResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Valeur pour les filtres et les formulaires, libellé pour l'affichage :
            // le français des enums reste côté PHP, jamais dupliqué dans Vue.
            'direction' => [
                'value' => $this->direction->value,
                'label' => $this->direction->label(),
            ],
            'reason' => [
                'value' => $this->reason->value,
                'label' => $this->reason->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'called_at' => $this->called_at->toIso8601String(),
            'duration_seconds' => $this->duration_seconds,
            'notes' => $this->notes,
            'client' => new ClientResource($this->whenLoaded('client')),
            'agent' => new AgentResource($this->whenLoaded('agent')),
            // Forme à rappel : `reservation_id` étant nullable, la relation peut
            // être chargée *et* valoir null. Sans ce garde, la resource tenterait
            // de formater les dates d'une réservation inexistante.
            'reservation' => $this->whenLoaded(
                'reservation',
                fn (): ?ReservationResource => $this->reservation === null
                    ? null
                    : new ReservationResource($this->reservation),
            ),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            // Décoratif : la CallPolicy reste la seule autorité côté serveur.
            'can' => [
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
