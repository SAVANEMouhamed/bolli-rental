<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardFilterRequest;
use App\Models\Call;
use App\Support\CallStatistics;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardFilterRequest $request): Response
    {
        $this->authorize('viewAny', Call::class);

        ['from' => $from, 'to' => $to] = $request->period();
        $granularity = $request->granularity();

        $statistics = new CallStatistics($from, $to);

        return Inertia::render('Dashboard', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'granularity' => $granularity,
            ],
            'summary' => $statistics->summary(),
            'volume' => $statistics->volume($granularity),
            'byReason' => $statistics->byReason(),
            'byStatus' => $statistics->byStatus(),
            'agentRanking' => $statistics->agentRanking(),
        ]);
    }
}
