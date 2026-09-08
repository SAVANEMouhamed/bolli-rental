<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Un tableau de bord sans borne de période finit par balayer toute la table :
 * la fenêtre est donc obligatoire, avec un défaut de 30 jours.
 */
class DashboardFilterRequest extends FormRequest
{
    private const DEFAULT_DAYS = 30;

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            // Liste blanche : la granularité pilote un regroupement, elle ne doit
            // jamais arriver telle quelle dans une expression SQL.
            'granularity' => ['nullable', Rule::in(['day', 'week'])],
        ];
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function period(): array
    {
        $to = $this->filled('to')
            ? CarbonImmutable::parse($this->date('to'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $from = $this->filled('from')
            ? CarbonImmutable::parse($this->date('from'))->startOfDay()
            : $to->subDays(self::DEFAULT_DAYS - 1)->startOfDay();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return 'day'|'week'
     */
    public function granularity(): string
    {
        return $this->string('granularity')->value() === 'week' ? 'week' : 'day';
    }
}
