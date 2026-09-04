<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;
use App\Models\Call;
use App\Support\CallStatistics;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    /**
     * Statistiques d'appels sur une période
     *
     * Mêmes agrégations que le tableau de bord web : elles viennent de la classe
     * CallStatistics, pas d'un second calcul qui pourrait diverger.
     */
    public function __invoke(DashboardFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Call::class);

        ['from' => $from, 'to' => $to] = $request->period();
        $granularity = $request->granularity();

        $statistics = new CallStatistics($from, $to);

        return response()->json([
            'data' => [
                'period' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'granularity' => $granularity,
                ],
                'summary' => $statistics->summary(),
                'volume' => $statistics->volume($granularity),
                'by_reason' => $statistics->byReason(),
                'by_status' => $statistics->byStatus(),
                'agent_ranking' => $statistics->agentRanking(),
            ],
        ]);
    }
}
