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
    $this->actingAs($this->agent);
});

function callIds(array $query): array
{
    $response = test()->get(route('calls.index', $query));
    $response->assertOk();

    return collect($response->viewData('page')['props']['calls']['data'])
        ->pluck('id')
        ->all();
}

it('filtre les appels par agent', function (): void {
    $mine = Call::factory()->for($this->agent, 'agent')->create();
    Call::factory()->create();

    expect(callIds(['agent' => $this->agent->id]))->toBe([$mine->id]);
});

it('filtre les appels par statut', function (): void {
    $escalated = Call::factory()->escalated()->create();
    Call::factory()->resolved()->create();

    expect(callIds(['status' => CallStatus::Escalated->value]))->toBe([$escalated->id]);
});

it('filtre les appels par motif', function (): void {
    $payment = Call::factory()->reason(CallReason::Payment)->create();
    Call::factory()->reason(CallReason::Support)->create();

    expect(callIds(['reason' => CallReason::Payment->value]))->toBe([$payment->id]);
});

it('filtre les appels par sens', function (): void {
    $outbound = Call::factory()->create(['direction' => CallDirection::Outbound]);
    Call::factory()->create(['direction' => CallDirection::Inbound]);

    expect(callIds(['direction' => CallDirection::Outbound->value]))->toBe([$outbound->id]);
});

it('filtre les appels par période', function (): void {
    $inside = Call::factory()->create(['called_at' => now()->subDays(3)]);
    Call::factory()->create(['called_at' => now()->subDays(40)]);

    $ids = callIds([
        'from' => now()->subWeek()->toDateString(),
        'to' => now()->toDateString(),
    ]);

    expect($ids)->toBe([$inside->id]);
});

it('filtre les appels par étiquette', function (): void {
    $tag = Tag::factory()->create(['name' => 'urgent', 'slug' => 'urgent']);
    $tagged = Call::factory()->create();
    $tagged->tags()->attach($tag);
    Call::factory()->create();

    expect(callIds(['tag' => 'urgent']))->toBe([$tagged->id]);
});

it('recherche un appel par le nom du client', function (): void {
    $client = Client::factory()->create(['first_name' => 'Aya', 'last_name' => 'Kouamé']);
    $found = Call::factory()->for($client)->create();

    // Client témoin nommé explicitement : la factory tire dans une liste de dix-huit
    // patronymes ivoiriens qui contient « Kouamé », et un témoin aléatoire faisait
    // échouer ce test une fois sur dix-huit.
    Call::factory()
        ->for(Client::factory()->create(['first_name' => 'Seydou', 'last_name' => 'Traoré']))
        ->create();

    expect(callIds(['search' => 'kouamé']))->toBe([$found->id]);
});

it('recherche un appel par le contenu des notes', function (): void {
    $found = Call::factory()->create(['notes' => 'Litige sur le paiement Wave.']);
    Call::factory()->create(['notes' => 'Rien à signaler.']);

    expect(callIds(['search' => 'wave']))->toBe([$found->id]);
});

it('combine plusieurs filtres', function (): void {
    $match = Call::factory()
        ->for($this->agent, 'agent')
        ->escalated()
        ->reason(CallReason::Complaint)
        ->create(['called_at' => now()->subDay()]);

    Call::factory()->for($this->agent, 'agent')->resolved()->create();
    Call::factory()->escalated()->reason(CallReason::Complaint)->create();

    $ids = callIds([
        'agent' => $this->agent->id,
        'status' => CallStatus::Escalated->value,
        'reason' => CallReason::Complaint->value,
    ]);

    expect($ids)->toBe([$match->id]);
});

it('rejette un statut hors de la liste autorisée', function (): void {
    $this->get(route('calls.index', ['status' => 'wibble']))
        ->assertSessionHasErrors('status');
});

it('rejette un agent inexistant', function (): void {
    $this->get(route('calls.index', ['agent' => 999_999]))
        ->assertSessionHasErrors('agent');
});

it('trie les appels du plus récent au plus ancien', function (): void {
    $older = Call::factory()->create(['called_at' => now()->subDays(5)]);
    $newer = Call::factory()->create(['called_at' => now()->subDay()]);

    expect(callIds([]))->toBe([$newer->id, $older->id]);
});

it('pagine la liste des appels', function (): void {
    Call::factory()->count(25)->create();

    $response = $this->get(route('calls.index'));
    $meta = $response->viewData('page')['props']['calls']['meta'];

    expect($meta['total'])->toBe(25)
        ->and($meta['per_page'])->toBe(10)
        ->and($meta['last_page'])->toBe(3);
});

it('filtre les appels rattachés à une réservation depuis sa fiche', function (): void {
    $reservation = Reservation::factory()->create();
    $attached = Call::factory()->forReservation($reservation)->create();
    Call::factory()->create();

    $response = $this->get(route('reservations.show', $reservation));
    $response->assertOk();

    $ids = collect($response->viewData('page')['props']['calls']['data'])->pluck('id')->all();

    expect($ids)->toBe([$attached->id]);
});
