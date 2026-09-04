<?php

declare(strict_types=1);

use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;

beforeEach(function (): void {
    $this->agent = User::factory()->create();
});

it("refuse l'API à un visiteur anonyme", function (): void {
    $this->getJson(route('api.v1.calls.index'))->assertUnauthorized();
    $this->getJson(route('api.v1.statistics'))->assertUnauthorized();
});

it('expose les appels paginés avec leurs relations', function (): void {
    $call = Call::factory()->for($this->agent, 'agent')->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'direction' => ['value', 'label'], 'reason', 'status', 'called_at', 'duration_seconds', 'client', 'agent', 'tags', 'can']],
            'links',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('data.0.id', $call->id)
        ->assertJsonPath('data.0.agent.name', $this->agent->name);
});

it("applique les mêmes filtres que l'écran web", function (): void {
    $match = Call::factory()->escalated()->reason(CallReason::Payment)->create();
    Call::factory()->resolved()->reason(CallReason::Support)->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.index', [
            'status' => CallStatus::Escalated->value,
            'reason' => CallReason::Payment->value,
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id);
});

it('rejette un filtre hors de la liste autorisée avec une erreur JSON', function (): void {
    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.index', ['status' => 'wibble']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('status');
});

it('respecte per_page', function (): void {
    Call::factory()->count(8)->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.index', ['per_page' => 3]))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 8);
});

it("expose le détail d'un appel", function (): void {
    $reservation = Reservation::factory()->create();
    $call = Call::factory()->forReservation($reservation)->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.show', $call))
        ->assertOk()
        ->assertJsonPath('id', $call->id)
        ->assertJsonPath('reservation.vehicle', $reservation->vehicle);
});

it("rend null la réservation d'un appel qui n'en a pas", function (): void {
    $call = Call::factory()->create(['reservation_id' => null]);

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.show', $call))
        ->assertOk()
        ->assertJsonPath('reservation', null);
});

it('renvoie 404 pour un appel inexistant', function (): void {
    $this->actingAs($this->agent)
        ->getJson(route('api.v1.calls.show', 999_999))
        ->assertNotFound();
});

it('expose les clients et leurs compteurs', function (): void {
    $client = Client::factory()->create();
    Call::factory()->count(2)->for($client)->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.clients.index'))
        ->assertOk()
        ->assertJsonPath('data.0.full_name', $client->full_name)
        ->assertJsonPath('data.0.calls_count', 2);
});

it('expose les réservations filtrables par statut', function (): void {
    $active = Reservation::factory()->active()->create();
    Reservation::factory()->cancelled()->create();

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.reservations.index', ['status' => 'active']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->id);
});

it('expose les mêmes statistiques que le tableau de bord', function (): void {
    Call::factory()->resolved()->count(3)->create([
        'called_at' => now()->subDay(),
        'duration_seconds' => 100,
    ]);

    $this->actingAs($this->agent)
        ->getJson(route('api.v1.statistics'))
        ->assertOk()
        ->assertJsonPath('summary.total', 3)
        ->assertJsonPath('summary.average_duration', 100)
        ->assertJsonStructure([
            'period', 'summary', 'volume' => ['labels', 'values'], 'by_reason', 'by_status', 'agent_ranking',
        ]);
});
