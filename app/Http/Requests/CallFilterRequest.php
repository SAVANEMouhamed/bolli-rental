<?php

namespace App\Http\Requests;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valide les filtres de liste avant qu'ils n'atteignent la requête SQL. Une valeur
 * hors des enums ou un identifiant d'agent inexistant est rejeté, jamais transmis
 * au constructeur de requête.
 */
class CallFilterRequest extends FormRequest
{
    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'agent' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'client' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'status' => ['nullable', Rule::enum(CallStatus::class)],
            'reason' => ['nullable', Rule::enum(CallReason::class)],
            'direction' => ['nullable', Rule::enum(CallDirection::class)],
            'tag' => ['nullable', 'string', Rule::exists('tags', 'slug')],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * Normalise les filtres avant de les rendre au contrôleur et au front.
     *
     * Deux corrections nécessaires : une valeur absente de l'URL y arrive en
     * chaîne vide, que `when()` doit ignorer ; et un identifiant d'agent arrive
     * en chaîne, alors que la liste déroulante Vue compare à des entiers — sans
     * cette conversion, le filtre appliqué n'apparaîtrait pas sélectionné.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = array_map(
            fn (mixed $value): mixed => $value === '' ? null : $value,
            $this->safe()->all(),
        );

        foreach (['agent', 'client'] as $key) {
            if (isset($filters[$key])) {
                $filters[$key] = (int) $filters[$key];
            }
        }

        return $filters;
    }
}
