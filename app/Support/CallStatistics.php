<?php

namespace App\Support;

use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agrégations du suivi des appels, partagées par le tableau de bord web et par
 * l'endpoint statistiques de l'API. C'est cette réutilisation réelle qui justifie
 * la classe : sans elle, la logique resterait dans le contrôleur.
 *
 * Tout est calculé par la base — COUNT, AVG, GROUP BY, jointure — et rien n'est
 * parcouru en PHP. Les requêtes s'appuient sur les index calls(called_at),
 * calls(user_id, called_at), calls(status, called_at) et calls(reason, called_at).
 */
class CallStatistics
{
    public function __construct(
        private readonly CarbonImmutable $from,
        private readonly CarbonImmutable $to,
    ) {}

    /**
     * Volume, durée moyenne et taux de résolution en une seule passe.
     *
     * @return array{total: int, average_duration: int, total_duration: int, resolved: int, escalated: int}
     */
    public function summary(): array
    {
        $row = $this->inPeriod()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(AVG(duration_seconds), 0) as average_duration')
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) as total_duration')
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as resolved', [CallStatus::Resolved->value])
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as escalated', [CallStatus::Escalated->value])
            ->toBase()
            ->first();

        return [
            'total' => (int) $row->total,
            'average_duration' => (int) round((float) $row->average_duration),
            'total_duration' => (int) $row->total_duration,
            'resolved' => (int) $row->resolved,
            'escalated' => (int) $row->escalated,
        ];
    }

    /**
     * Série temporelle prête à tracer.
     *
     * Le regroupement SQL se fait au jour : `DATE()` est la seule fonction de date
     * comprise à l'identique par PostgreSQL (production) et SQLite (tests). Le
     * cumul hebdomadaire porte ensuite sur ces compteurs journaliers — au plus
     * quelques dizaines de lignes déjà agrégées, jamais les appels eux-mêmes.
     *
     * L'application tourne en UTC et Abidjan est à UTC+0 : la date SQL est bien
     * la date locale de l'agent.
     *
     * @param  'day'|'week'  $granularity
     * @return array{labels: list<string>, values: list<int>}
     */
    public function volume(string $granularity): array
    {
        $daily = $this->inPeriod()
            ->selectRaw('DATE(called_at) as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->toBase()
            ->pluck('total', 'bucket');

        // Un jour sans appel n'a pas de ligne en base : sans ce remplissage, la
        // courbe relierait deux dates éloignées et mentirait sur l'activité.
        $series = [];

        for ($day = $this->from->startOfDay(); $day->lessThanOrEqualTo($this->to); $day = $day->addDay()) {
            $key = $granularity === 'week'
                ? $day->startOfWeek()->toDateString()
                : $day->toDateString();

            $series[$key] = ($series[$key] ?? 0) + (int) ($daily[$day->toDateString()] ?? 0);
        }

        return [
            'labels' => array_keys($series),
            'values' => array_values($series),
        ];
    }

    /**
     * @return list<array{value: string, label: string, total: int}>
     */
    public function byReason(): array
    {
        return $this->distribution('reason', CallReason::class);
    }

    /**
     * @return list<array{value: string, label: string, total: int}>
     */
    public function byStatus(): array
    {
        return $this->distribution('status', CallStatus::class);
    }

    /**
     * Classement des agents par volume traité.
     *
     * Jointure plutôt que `with('agent')` : une seule requête au lieu de deux, et
     * des lignes brutes plutôt que des modèles Call porteurs d'attributs agrégés
     * qui n'existent pas sur l'entité.
     *
     * @return list<array{id: int, name: string, total: int, average_duration: int}>
     */
    public function agentRanking(): array
    {
        $rows = $this->inPeriod()
            ->join('users', 'users.id', '=', 'calls.user_id')
            ->selectRaw('users.id as id, users.name as name, COUNT(*) as total, COALESCE(AVG(duration_seconds), 0) as average_duration')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->toBase()
            ->get()
            ->all();

        return array_values(array_map(
            fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'total' => (int) $row->total,
                'average_duration' => (int) round((float) $row->average_duration),
            ],
            $rows,
        ));
    }

    /**
     * @return Builder<Call>
     */
    private function inPeriod(): Builder
    {
        return Call::query()->whereBetween('called_at', [$this->from, $this->to]);
    }

    /**
     * Répartition complétée des cas absents, pour que le graphique montre les cinq
     * motifs même quand l'un d'eux est à zéro.
     *
     * @param  'reason'|'status'  $column
     * @param  class-string<CallReason|CallStatus>  $enum
     * @return list<array{value: string, label: string, total: int}>
     */
    private function distribution(string $column, string $enum): array
    {
        // Aucune interpolation dans le SQL : la colonne est choisie parmi deux
        // expressions littérales écrites ici, pas construite à partir d'une variable.
        $select = match ($column) {
            'reason' => 'reason as bucket, COUNT(*) as total',
            'status' => 'status as bucket, COUNT(*) as total',
        };

        $totals = $this->inPeriod()
            ->selectRaw($select)
            ->groupBy('bucket')
            ->toBase()
            ->pluck('total', 'bucket');

        return array_map(
            fn (array $option): array => [
                ...$option,
                'total' => (int) ($totals[$option['value']] ?? 0),
            ],
            $enum::options(),
        );
    }
}
