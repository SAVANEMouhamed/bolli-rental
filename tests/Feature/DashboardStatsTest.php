<?php

declare(strict_types=1);

use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\User;

beforeEach(function (): void {
    $this->agent = User::factory()->create();
    $this->actingAs($this->agent);
});

function dashboardProps(array $query = []): array
{
    $response = test()->get(route('dashboard', $query));
    $response->assertOk();

    return $response->viewData('page')['props'];
}

it('calcule le volume, la durée moyenne et le taux de résolution', function (): void {
    Call::factory()->resolved()->count(3)->create([
        'called_at' => now()->subDay(),
        'duration_seconds' => 100,
    ]);
    Call::factory()->escalated()->create([
        'called_at' => now()->subDay(),
        'duration_seconds' => 500,
    ]);

    $summary = dashboardProps()['summary'];

    expect($summary['total'])->toBe(4)
        ->and($summary['resolved'])->toBe(3)
        ->and($summary['escalated'])->toBe(1)
        // (100 + 100 + 100 + 500) / 4
        ->and($summary['average_duration'])->toBe(200);
});

it('exclut les appels hors de la période analysée', function (): void {
    Call::factory()->create(['called_at' => now()->subDays(2)]);
    Call::factory()->create(['called_at' => now()->subDays(200)]);

    expect(dashboardProps()['summary']['total'])->toBe(1);
});

it('respecte une période explicite', function (): void {
    Call::factory()->create(['called_at' => now()->subDays(100)]);
    Call::factory()->create(['called_at' => now()->subDay()]);

    $summary = dashboardProps([
        'from' => now()->subDays(120)->toDateString(),
        'to' => now()->subDays(90)->toDateString(),
    ])['summary'];

    expect($summary['total'])->toBe(1);
});

it('répartit les appels par motif en incluant les motifs à zéro', function (): void {
    Call::factory()->reason(CallReason::Payment)->count(2)->create(['called_at' => now()->subDay()]);

    $byReason = collect(dashboardProps()['byReason'])->keyBy('value');

    expect($byReason)->toHaveCount(count(CallReason::cases()))
        ->and($byReason[CallReason::Payment->value]['total'])->toBe(2)
        ->and($byReason[CallReason::Support->value]['total'])->toBe(0);
});

it('répartit les appels par statut', function (): void {
    Call::factory()->pending()->count(4)->create(['called_at' => now()->subDay()]);

    $byStatus = collect(dashboardProps()['byStatus'])->keyBy('value');

    expect($byStatus[CallStatus::Pending->value]['total'])->toBe(4)
        ->and($byStatus[CallStatus::Resolved->value]['total'])->toBe(0);
});

it('classe les agents par volume traité, du plus actif au moins actif', function (): void {
    $busy = User::factory()->create(['name' => 'Aïcha']);
    $quiet = User::factory()->create(['name' => 'Serge']);

    Call::factory()->for($busy, 'agent')->count(3)->create(['called_at' => now()->subDay()]);
    Call::factory()->for($quiet, 'agent')->create(['called_at' => now()->subDay()]);

    $ranking = dashboardProps()['agentRanking'];

    expect($ranking)->toHaveCount(2)
        ->and($ranking[0]['name'])->toBe('Aïcha')
        ->and($ranking[0]['total'])->toBe(3)
        ->and($ranking[1]['name'])->toBe('Serge');
});

it('remplit les jours sans appel pour que la courbe ne mente pas', function (): void {
    Call::factory()->create(['called_at' => now()->subDays(3)->setTime(10, 0)]);

    $volume = dashboardProps([
        'from' => now()->subDays(5)->toDateString(),
        'to' => now()->toDateString(),
    ])['volume'];

    // Six jours de bornes incluses, un seul porteur d'appel.
    expect($volume['labels'])->toHaveCount(6)
        ->and($volume['values'])->toHaveCount(6)
        ->and(array_sum($volume['values']))->toBe(1);
});

it('cumule le volume par semaine quand la granularité le demande', function (): void {
    Call::factory()->count(2)->create(['called_at' => now()->startOfWeek()->addDay()->setTime(10, 0)]);

    $volume = dashboardProps([
        'from' => now()->startOfWeek()->toDateString(),
        'to' => now()->startOfWeek()->addDays(6)->toDateString(),
        'granularity' => 'week',
    ])['volume'];

    expect($volume['labels'])->toHaveCount(1)
        ->and($volume['values'][0])->toBe(2);
});

it('rejette une granularité hors de la liste blanche', function (): void {
    $this->get(route('dashboard', ['granularity' => 'century']))
        ->assertSessionHasErrors('granularity');
});

it('affiche un tableau de bord vide sans erreur', function (): void {
    $props = dashboardProps();

    expect($props['summary']['total'])->toBe(0)
        ->and($props['summary']['average_duration'])->toBe(0)
        ->and($props['agentRanking'])->toBe([]);
});
