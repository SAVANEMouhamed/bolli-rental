<?php

namespace App\Http\Controllers;

use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Http\Requests\DashboardFilterRequest;
use App\Models\Call;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Toutes les statistiques sont calculées par la base : COUNT, AVG et GROUP BY.
 * Aucune collection d'appels n'est chargée en mémoire pour être parcourue en PHP.
 *
 * Les requêtes s'appuient sur les index `calls(called_at)`, `calls(user_id,
 * called_at)`, `calls(status, called_at)` et `calls(reason, called_at)`.
 */
class DashboardController extends Controller
{
    public function __invoke(DashboardFilterRequest $request): Response
    {
        $this->authorize('viewAny', Call::class);

        ['from' => $from, 'to' => $to] = $request->period();
        $granularity = $request->granularity();

        return Inertia::render('Dashboard', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'granularity' => $granularity,
            ],
            'summary' => $this->summary($from, $to),
            'volume' => $this->volume($from, $to, $granularity),
            'byReason' => $this->distribution($from, $to, 'reason', CallReason::class),
            'byStatus' => $this->distribution($from, $to, 'status', CallStatus::class),
            'agentRanking' => $this->agentRanking($from, $to),
        ]);
    }

    /**
     * @return Builder<Call>
     */
    private function inPeriod(CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return Call::query()->whereBetween('called_at', [$from, $to]);
    }

    /**
     * Volume, durée moyenne et taux de résolution en une seule passe.
     *
     * @return array{total: int, average_duration: int, total_duration: int, resolved: int, escalated: int}
     */
    private function summary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = $this->inPeriod($from, $to)
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
     * Le regroupement SQL se fait au jour — `DATE()` est la seule fonction de date
     * comprise à l'identique par PostgreSQL (production) et SQLite (tests). Le
     * cumul hebdomadaire se fait ensuite sur ces compteurs journaliers : au plus
     * quelques dizaines de lignes déjà agrégées, jamais les appels eux-mêmes.
     *
     * L'application tourne en UTC et Abidjan est à UTC+0 : la date SQL est bien
     * la date locale de l'agent.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function volume(CarbonImmutable $from, CarbonImmutable $to, string $granularity): array
    {
        $daily = $this->inPeriod($from, $to)
            ->selectRaw('DATE(called_at) as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->toBase()
            ->pluck('total', 'bucket')
            ->map(fn (mixed $total): int => (int) $total);

        // Un jour sans appel n'a pas de ligne en base : sans ce remplissage, la
        // courbe relierait deux dates éloignées et mentirait sur l'activité.
        $series = [];

        for ($day = $from->startOfDay(); $day->lessThanOrEqualTo($to); $day = $day->addDay()) {
            $key = $granularity === 'week'
                ? $day->startOfWeek()->toDateString()
                : $day->toDateString();

            $series[$key] = ($series[$key] ?? 0) + ($daily[$day->toDateString()] ?? 0);
        }

        return [
            'labels' => array_keys($series),
            'values' => array_values($series),
        ];
    }

    /**
     * Répartition par motif ou par statut, complétée des cas absents pour que le
     * graphique montre les cinq motifs même quand l'un d'eux est à zéro.
     *
     * @param  'reason'|'status'  $column
     * @param  class-string<CallReason|CallStatus>  $enum
     * @return list<array{value: string, label: string, total: int}>
     */
    private function distribution(CarbonImmutable $from, CarbonImmutable $to, string $column, string $enum): array
    {
        // Aucune interpolation dans le SQL : la colonne est choisie parmi deux
        // expressions littérales écrites ici, pas construite à partir d'une variable.
        $select = match ($column) {
            'reason' => 'reason as bucket, COUNT(*) as total',
            'status' => 'status as bucket, COUNT(*) as total',
        };

        $totals = $this->inPeriod($from, $to)
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

    /**
     * Classement des agents par volume traité sur la période.
     *
     * Jointure plutôt que `with('agent')` : une seule requête au lieu de deux, et
     * des lignes brutes plutôt que des modèles Call porteurs d'attributs agrégés
     * qui n'existent pas sur l'entité.
     *
     * @return list<array{id: int, name: string, total: int, average_duration: int}>
     */
    private function agentRanking(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = $this->inPeriod($from, $to)
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
}
