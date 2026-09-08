<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    /**
     * Lister les clients
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('search')->trim()->value();

        $clients = Client::query()
            ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
            ->withCount(['calls', 'reservations'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderBy('last_name')
            ->paginate($request->integer('per_page') ?: 25)
            ->withQueryString();

        return ClientResource::collection($clients);
    }

    /**
     * Consulter un client et ses réservations
     */
    public function show(Client $client): ClientResource
    {
        $client->loadCount(['calls', 'reservations']);
        $client->load([
            'reservations' => fn ($query) => $query
                ->select(['id', 'client_id', 'vehicle', 'starts_at', 'ends_at', 'status'])
                ->withCount('calls')
                ->latest('starts_at'),
        ]);

        return new ClientResource($client);
    }
}
