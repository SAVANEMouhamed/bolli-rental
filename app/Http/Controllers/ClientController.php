<?php

namespace App\Http\Controllers;

use App\Http\Resources\CallResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Consultation seule : le cahier des charges alimente les clients par seeders et
 * ne demande pas de module de gestion (§2b).
 */
class ClientController extends Controller
{
    /**
     * Dix lignes par page sur tous les écrans de liste : une hauteur d'écran se lit
     * sans défilement, et la pagination reste visible sans avoir à descendre.
     */
    private const PER_PAGE = 10;

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $clients = Client::query()
            ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
            ->withCount(['calls', 'reservations'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderBy('last_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('clients/Index', [
            'clients' => ClientResource::collection($clients),
            'filters' => ['search' => $search],
        ]);
    }

    public function show(Client $client): Response
    {
        $client->load([
            'reservations' => fn ($query) => $query
                ->select(['id', 'client_id', 'vehicle', 'starts_at', 'ends_at', 'status'])
                ->withCount('calls')
                ->latest('starts_at'),
        ]);

        $calls = $client->calls()
            ->with(['agent:id,name', 'reservation', 'tags:id,name,slug'])
            ->latest('called_at')
            ->paginate(self::PER_PAGE);

        return Inertia::render('clients/Show', [
            'client' => new ClientResource($client),
            'calls' => CallResource::collection($calls),
        ]);
    }
}
