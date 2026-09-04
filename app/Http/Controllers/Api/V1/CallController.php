<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallFilterRequest;
use App\Http\Resources\CallResource;
use App\Models\Call;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CallController extends Controller
{
    /**
     * Lister les appels
     *
     * Retourne les appels du service client, du plus récent au plus ancien.
     * Accepte les mêmes filtres que l'écran web — ils sont portés par le scope
     * `Call::filtered()`, donc les deux surfaces ne peuvent pas diverger.
     */
    public function index(CallFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Call::class);

        $calls = Call::query()
            ->with([
                'client',
                'agent:id,name',
                'reservation',
                'tags:id,name,slug',
            ])
            ->filtered($request->filters())
            ->latest('called_at')
            ->paginate($request->integer('per_page') ?: 25)
            ->withQueryString();

        return CallResource::collection($calls);
    }

    /**
     * Consulter un appel
     */
    public function show(Call $call): CallResource
    {
        $this->authorize('view', $call);

        $call->load([
            'client',
            'agent:id,name',
            'reservation',
            'tags:id,name,slug',
        ]);

        return new CallResource($call);
    }
}
