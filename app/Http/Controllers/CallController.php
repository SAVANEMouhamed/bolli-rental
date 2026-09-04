<?php

namespace App\Http\Controllers;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Http\Requests\CallFilterRequest;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Http\Resources\AgentResource;
use App\Http\Resources\CallResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\TagResource;
use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CallController extends Controller
{
    public function index(CallFilterRequest $request): Response
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
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('calls/Index', [
            'calls' => CallResource::collection($calls),
            'filters' => $request->filters(),
            'options' => $this->filterOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Call::class);

        return Inertia::render('calls/Create', $this->formProps($request));
    }

    public function store(StoreCallRequest $request): RedirectResponse
    {
        $call = Call::create([
            ...$request->safe()->except('tags'),
            // Jamais fourni par le client HTTP : un agent ne crédite pas un collègue
            // d'un appel, ce qui fausserait le classement du tableau de bord.
            'user_id' => $request->user()->id,
        ]);

        $call->tags()->sync($request->validated('tags', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Appel enregistré.']);

        return to_route('calls.show', $call);
    }

    public function show(Call $call): Response
    {
        $this->authorize('view', $call);

        $call->load([
            'client',
            'agent:id,name',
            'reservation',
            'tags:id,name,slug',
        ]);

        return Inertia::render('calls/Show', [
            // `resolve()` plutôt que la resource brute : une JsonResource seule
            // s'enveloppe dans une clé `data`, et la page attend l'appel lui-même.
            // Les collections paginées, elles, gardent `data`/`links`/`meta`.
            'call' => (new CallResource($call))->resolve(),
        ]);
    }

    public function edit(Request $request, Call $call): Response
    {
        $this->authorize('update', $call);

        $call->load(['client', 'tags:id,name,slug']);

        return Inertia::render('calls/Edit', [
            ...$this->formProps($request, $call->client_id),
            'call' => (new CallResource($call))->resolve(),
        ]);
    }

    public function update(UpdateCallRequest $request, Call $call): RedirectResponse
    {
        $call->update($request->safe()->except('tags'));
        $call->tags()->sync($request->validated('tags', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Appel mis à jour.']);

        return to_route('calls.show', $call);
    }

    public function destroy(Call $call): RedirectResponse
    {
        $this->authorize('delete', $call);

        $call->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Appel supprimé.']);

        return to_route('calls.index');
    }

    /**
     * Listes fermées alimentant les filtres et les formulaires.
     *
     * @return array<string, mixed>
     */
    private function filterOptions(): array
    {
        return [
            'directions' => CallDirection::options(),
            'reasons' => CallReason::options(),
            'statuses' => CallStatus::options(),
            'agents' => AgentResource::collection(
                User::query()->select(['id', 'name'])->orderBy('name')->get()
            ),
            'tags' => TagResource::collection(
                Tag::query()->select(['id', 'name', 'slug'])->orderBy('name')->get()
            ),
        ];
    }

    /**
     * Props du formulaire d'appel. La liste des clients est filtrée par la recherche
     * saisie côté Vue et rechargée par visite partielle Inertia : ni `<select>` de
     * plusieurs centaines d'entrées, ni endpoint JSON parallèle à l'application.
     *
     * @return array<string, mixed>
     */
    private function formProps(Request $request, ?int $clientId = null): array
    {
        $search = $request->string('client_search')->trim()->value();
        $selectedClient = $request->integer('client_id') ?: $clientId;

        $clients = Client::query()
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->orderBy('last_name')
            ->limit(20)
            ->get();

        // Les réservations proposées sont celles du client choisi : la Form Request
        // revérifie ce lien, la liste n'est qu'un confort de saisie.
        $reservations = $selectedClient === null || $selectedClient === 0
            ? collect()
            : Reservation::query()
                ->select(['id', 'vehicle', 'starts_at', 'ends_at', 'status', 'client_id'])
                ->where('client_id', $selectedClient)
                ->latest('starts_at')
                ->get();

        return [
            ...$this->filterOptions(),
            'clientSearch' => $search,
            'clients' => ClientResource::collection($clients),
            'reservations' => ReservationResource::collection($reservations),
        ];
    }
}
