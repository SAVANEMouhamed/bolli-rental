<?php

namespace App\Http\Requests;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Call::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            // Règle métier : un appel ne peut pas être rattaché à la location d'un
            // autre client. Vérifié en base, pas seulement dans la liste déroulante.
            'reservation_id' => [
                'nullable',
                'integer',
                Rule::exists('reservations', 'id')
                    ->where('client_id', $this->integer('client_id')),
            ],
            'direction' => ['required', Rule::enum(CallDirection::class)],
            'reason' => ['required', Rule::enum(CallReason::class)],
            'status' => ['required', Rule::enum(CallStatus::class)],
            'called_at' => ['required', 'date', 'before_or_equal:now'],
            // 24 h : au-delà, c'est une faute de frappe, pas un appel.
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reservation_id.exists' => 'Cette réservation n\'appartient pas au client sélectionné.',
            'called_at.before_or_equal' => 'Un appel ne peut pas être enregistré dans le futur.',
        ];
    }
}
