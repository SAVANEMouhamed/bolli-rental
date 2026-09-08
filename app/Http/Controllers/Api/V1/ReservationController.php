<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Lister les réservations
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->validate([
            'status' => ['nullable', Rule::enum(ReservationStatus::class)],
        ])['status'] ?? null;

        $reservations = Reservation::query()
            ->select(['id', 'client_id', 'vehicle', 'starts_at', 'ends_at', 'status'])
            ->with('client')
            ->withCount('calls')
            ->when($status, fn ($query, string $value) => $query->where('status', $value))
            ->latest('starts_at')
            ->paginate($request->integer('per_page') ?: 25)
            ->withQueryString();

        return ReservationResource::collection($reservations);
    }

    /**
     * Consulter une réservation
     */
    public function show(Reservation $reservation): ReservationResource
    {
        $reservation->loadCount('calls');
        $reservation->load('client');

        return new ReservationResource($reservation);
    }
}
