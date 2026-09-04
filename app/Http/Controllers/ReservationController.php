<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Resources\CallResource;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Consultation seule, comme les clients : les réservations viennent des seeders
 * et servent de contexte aux appels (§2b du cahier des charges).
 */
class ReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->validate([
            'status' => ['nullable', Rule::enum(ReservationStatus::class)],
        ])['status'] ?? null;

        $reservations = Reservation::query()
            ->select(['id', 'client_id', 'vehicle', 'starts_at', 'ends_at', 'status'])
            ->with('client:id,first_name,last_name,phone')
            ->withCount('calls')
            ->when($status, fn ($query, string $value) => $query->where('status', $value))
            ->latest('starts_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('reservations/Index', [
            'reservations' => ReservationResource::collection($reservations),
            'filters' => ['status' => $status],
            'options' => ['statuses' => ReservationStatus::options()],
        ]);
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load('client:id,first_name,last_name,phone,email');

        $calls = $reservation->calls()
            ->with(['agent:id,name', 'client:id,first_name,last_name,phone', 'tags:id,name,slug'])
            ->latest('called_at')
            ->paginate(10);

        return Inertia::render('reservations/Show', [
            'reservation' => (new ReservationResource($reservation))->resolve(),
            'calls' => CallResource::collection($calls),
        ]);
    }
}
