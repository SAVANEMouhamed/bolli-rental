<?php

declare(strict_types=1);

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Models\User;

it("protège la documentation derrière l'authentification", function (): void {
    // L'interface s'adresse à un humain : redirection vers la connexion.
    $this->get(route('api.documentation'))->assertRedirect(route('login'));
    // Le document s'adresse à une machine : 401 JSON.
    $this->getJson(route('api.documentation.schema'))->assertUnauthorized();
});

it('sert une interface Swagger', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('api.documentation'))
        ->assertOk()
        ->assertSee('swagger-ui', escape: false);
});

it('publie un document OpenAPI 3.1 décrivant chaque endpoint exposé', function (): void {
    $schema = $this->actingAs(User::factory()->create())
        ->getJson(route('api.documentation.schema'))
        ->assertOk()
        ->json();

    expect($schema['openapi'])->toBe('3.1.0')
        ->and(array_keys($schema['paths']))->toEqualCanonicalizing([
            '/api/v1/calls',
            '/api/v1/calls/{call}',
            '/api/v1/clients',
            '/api/v1/clients/{client}',
            '/api/v1/reservations',
            '/api/v1/reservations/{reservation}',
            '/api/v1/statistics',
        ]);
});

/**
 * Le document lit les valeurs sur les enums de l'application. Ce test verrouille
 * ce lien : ajouter un motif d'appel met la documentation à jour toute seule, et
 * la divergence devient impossible.
 */
it('documente les mêmes valeurs de filtre que les enums du domaine', function (): void {
    $schema = $this->actingAs(User::factory()->create())
        ->getJson(route('api.documentation.schema'))
        ->json();

    $parameters = collect($schema['paths']['/api/v1/calls']['get']['parameters'])
        ->keyBy('name');

    expect($parameters['status']['schema']['enum'])
        ->toBe(array_column(CallStatus::options(), 'value'))
        ->and($parameters['reason']['schema']['enum'])
        ->toBe(array_column(CallReason::options(), 'value'))
        ->and($parameters['direction']['schema']['enum'])
        ->toBe(array_column(CallDirection::options(), 'value'));
});
