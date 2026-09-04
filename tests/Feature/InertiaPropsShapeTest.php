<?php

declare(strict_types=1);

use App\Models\Call;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;

/**
 * Régression : une JsonResource passée en prop Inertia s'enveloppait dans une clé
 * `data`. `options.agents` arrivait donc en `{data: [...]}` là où la page Vue
 * appelle `.map()`, et l'écran des appels — le cœur de l'exercice — s'affichait
 * entièrement blanc, sans erreur serveur puisque la requête répondait 200.
 *
 * Ces tests figent la forme des props : ce sont elles, et non le code de rendu
 * Vue, que la suite peut vérifier côté serveur.
 */
beforeEach(function (): void {
    $this->agent = User::factory()->create();
    Tag::factory()->create();
});

it('envoie les listes de filtres des appels comme des tableaux', function (): void {
    Call::factory()->for($this->agent, 'agent')->create();

    $this->actingAs($this->agent)
        ->get(route('calls.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('options.agents', 1)
            ->where('options.agents.0.name', $this->agent->name)
            ->has('options.tags', 1)
            ->missing('options.agents.data')
            ->missing('options.tags.data')
            // La pagination, elle, garde son enveloppe : `links` et `meta` doivent
            // voisiner avec les lignes.
            ->has('calls.data')
            ->has('calls.meta.total')
        );
});

it('envoie les listes du formulaire d\'appel comme des tableaux', function (): void {
    $client = Client::factory()->create();
    Reservation::factory()->for($client)->create();

    $this->actingAs($this->agent)
        ->get(route('calls.create', ['client_id' => $client->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('clients', 1)
            ->has('reservations', 1)
            ->has('agents', 1)
            ->has('tags', 1)
            ->missing('clients.data')
            ->missing('reservations.data')
        );
});

it('envoie le détail d\'un appel sans enveloppe sur ses relations', function (): void {
    $reservation = Reservation::factory()->create();
    $call = Call::factory()->for($this->agent, 'agent')->forReservation($reservation)->create();
    $call->tags()->attach(Tag::query()->value('id'));

    $this->actingAs($this->agent)
        ->get(route('calls.show', $call))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('call.id', $call->id)
            ->where('call.client.full_name', $call->client->full_name)
            ->where('call.agent.name', $this->agent->name)
            ->where('call.reservation.vehicle', $reservation->vehicle)
            ->has('call.tags', 1)
            ->missing('call.client.data')
            ->missing('call.tags.data')
        );
});

it('envoie la fiche client avec ses réservations en tableau', function (): void {
    $client = Client::factory()->create();
    $reservation = Reservation::factory()->for($client)->create();

    $this->actingAs($this->agent)
        ->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('client.full_name', $client->full_name)
            ->has('client.reservations', 1)
            ->where('client.reservations.0.vehicle', $reservation->vehicle)
            ->missing('client.reservations.data')
        );
});

it('envoie la fiche réservation avec son client sans enveloppe', function (): void {
    $client = Client::factory()->create();
    $reservation = Reservation::factory()->for($client)->create();

    $this->actingAs($this->agent)
        ->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reservation.vehicle', $reservation->vehicle)
            ->where('reservation.client.full_name', $client->full_name)
            ->missing('reservation.client.data')
        );
});
