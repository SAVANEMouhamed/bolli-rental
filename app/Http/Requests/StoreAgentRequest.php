<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Tous les agents du plateau sont pairs : il n'y a pas de rôle dans le
     * périmètre du cahier des charges. La protection réelle est la confirmation
     * du mot de passe exigée par la route.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return $this->profileRules();
    }
}
