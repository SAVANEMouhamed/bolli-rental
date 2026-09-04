<?php

declare(strict_types=1);

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;

beforeEach(function (): void {
    $this->agent = User::factory()->create();
});

it('interdit la liste des appels à un visiteur anonyme', function (): void {
    $this->get(route('calls.index'))->assertRedirect(route('login'));
});

it('liste les appels avec leurs relations chargées', function (): void {
    $call = Call::factory()->for($this->agent, 'agent')->create();

    $this->actingAs($this->agent)
        ->get(route('calls.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calls/Index')
            ->has('calls.data', 1)
            ->where('calls.data.0.id', $call->id)
            ->where('calls.data.0.client.full_name', $call->client->full_name)
            ->where('calls.data.0.agent.name', $this->agent->name)
        );
});

/**
 * Régression : la liste ne chargeait la réservation que partiellement, sans ses
 * dates, que la resource formate systématiquement. Le défaut ne se voyait pas
 * parce qu'aucun test ne listait un appel effectivement rattaché à une location.
 */
it('liste sans erreur un appel rattaché à une réservation', function (): void {
    $reservation = Reservation::factory()->create();
    $call = Call::factory()->forReservation($reservation)->create();

    $this->actingAs($this->agent)
        ->get(route('calls.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('calls.data.0.id', $call->id)
            ->where('calls.data.0.reservation.vehicle', $reservation->vehicle)
        );
});

it('liste sans erreur un appel dont le client n\'a pas d\'adresse e-mail', function (): void {
    $client = Client::factory()->create(['email' => null]);
    Call::factory()->for($client)->create();

    $this->actingAs($this->agent)
        ->get(route('calls.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('calls.data.0.client.email', null));
});

it('enregistre un appel et le crédite à l\'agent connecté', function (): void {
    $client = Client::factory()->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($this->agent)->post(route('calls.store'), [
        'client_id' => $client->id,
        'reservation_id' => null,
        'direction' => CallDirection::Inbound->value,
        'reason' => CallReason::Payment->value,
        'status' => CallStatus::Pending->value,
        'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
        'duration_seconds' => 252,
        'notes' => 'Problème de paiement mobile money.',
        'tags' => [$tag->id],
    ]);

    $call = Call::query()->sole();

    $response->assertRedirect(route('calls.show', $call));

    expect($call->user_id)->toBe($this->agent->id)
        ->and($call->duration_seconds)->toBe(252)
        ->and($call->reason)->toBe(CallReason::Payment)
        ->and($call->tags)->toHaveCount(1);
});

it('ignore un agent imposé par le client HTTP', function (): void {
    $someoneElse = User::factory()->create();
    $client = Client::factory()->create();

    $this->actingAs($this->agent)->post(route('calls.store'), [
        'client_id' => $client->id,
        'direction' => CallDirection::Inbound->value,
        'reason' => CallReason::Other->value,
        'status' => CallStatus::Resolved->value,
        'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
        'duration_seconds' => 60,
        // Tentative de créditer un collègue, ce qui fausserait le classement.
        'user_id' => $someoneElse->id,
    ]);

    expect(Call::query()->sole()->user_id)->toBe($this->agent->id);
});

it('refuse un appel dont la réservation appartient à un autre client', function (): void {
    $client = Client::factory()->create();
    $otherReservation = Reservation::factory()->create();

    $this->actingAs($this->agent)
        ->post(route('calls.store'), [
            'client_id' => $client->id,
            'reservation_id' => $otherReservation->id,
            'direction' => CallDirection::Inbound->value,
            'reason' => CallReason::Reservation->value,
            'status' => CallStatus::Resolved->value,
            'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'duration_seconds' => 60,
        ])
        ->assertSessionHasErrors('reservation_id');

    expect(Call::query()->count())->toBe(0);
});

it('rattache un appel à une réservation du bon client', function (): void {
    $reservation = Reservation::factory()->create();

    $this->actingAs($this->agent)->post(route('calls.store'), [
        'client_id' => $reservation->client_id,
        'reservation_id' => $reservation->id,
        'direction' => CallDirection::Outbound->value,
        'reason' => CallReason::Reservation->value,
        'status' => CallStatus::Resolved->value,
        'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
        'duration_seconds' => 120,
    ]);

    expect(Call::query()->sole()->reservation_id)->toBe($reservation->id);
});

it('refuse un appel enregistré dans le futur', function (): void {
    $client = Client::factory()->create();

    $this->actingAs($this->agent)
        ->post(route('calls.store'), [
            'client_id' => $client->id,
            'direction' => CallDirection::Inbound->value,
            'reason' => CallReason::Other->value,
            'status' => CallStatus::Resolved->value,
            'called_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_seconds' => 60,
        ])
        ->assertSessionHasErrors('called_at');
});

it('refuse un payload vide avec les bons champs en erreur', function (): void {
    $this->actingAs($this->agent)
        ->post(route('calls.store'), [])
        ->assertSessionHasErrors([
            'client_id',
            'direction',
            'reason',
            'status',
            'called_at',
            'duration_seconds',
        ]);
});

it('affiche le détail d\'un appel', function (): void {
    $call = Call::factory()->for($this->agent, 'agent')->create();

    $this->actingAs($this->agent)
        ->get(route('calls.show', $call))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calls/Show')
            ->where('call.id', $call->id)
            ->where('call.can.update', true)
        );
});

it('interdit à un agent de modifier l\'appel d\'un collègue', function (): void {
    $call = Call::factory()->create();

    $this->actingAs($this->agent)
        ->get(route('calls.edit', $call))
        ->assertForbidden();

    $this->actingAs($this->agent)
        ->put(route('calls.update', $call), [
            'client_id' => $call->client_id,
            'direction' => CallDirection::Inbound->value,
            'reason' => CallReason::Other->value,
            'status' => CallStatus::Resolved->value,
            'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'duration_seconds' => 60,
        ])
        ->assertForbidden();
});

it('interdit à un agent de supprimer l\'appel d\'un collègue', function (): void {
    $call = Call::factory()->create();

    $this->actingAs($this->agent)
        ->delete(route('calls.destroy', $call))
        ->assertForbidden();

    expect(Call::query()->whereKey($call->id)->exists())->toBeTrue();
});

it('laisse un agent corriger et supprimer son propre appel', function (): void {
    $call = Call::factory()->for($this->agent, 'agent')->create();

    $this->actingAs($this->agent)
        ->put(route('calls.update', $call), [
            'client_id' => $call->client_id,
            'direction' => CallDirection::Outbound->value,
            'reason' => CallReason::Complaint->value,
            'status' => CallStatus::Escalated->value,
            'called_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'duration_seconds' => 900,
        ])
        ->assertRedirect(route('calls.show', $call));

    expect($call->fresh()->status)->toBe(CallStatus::Escalated);

    $this->actingAs($this->agent)
        ->delete(route('calls.destroy', $call))
        ->assertRedirect(route('calls.index'));

    expect(Call::query()->count())->toBe(0);
});
