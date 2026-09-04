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
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $clients = Client::query()
            ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
            ->withCount(['calls', 'reservations'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderBy('last_name')
            ->paginate(20)
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
            ->with(['agent:id,name', 'reservation:id,vehicle,client_id', 'tags:id,name,slug'])
            ->latest('called_at')
            ->paginate(10);

        return Inertia::render('clients/Show', [
            'client' => (new ClientResource($client))->resolve(),
            'calls' => CallResource::collection($calls),
        ]);
    }
}
