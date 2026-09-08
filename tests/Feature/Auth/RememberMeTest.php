<?php

declare(strict_types=1);

use App\Models\User;

/**
 * Le formulaire de connexion poste toujours le champ `remember` : « 1 » quand la
 * case est cochée, « 0 » sinon. Ces tests figent ce contrat, car un champ absent
 * ou une valeur « on » parasite rendait l'agent connecté durablement sans l'avoir
 * demandé.
 */
it("ne pose pas de cookie « rester connecté » quand la case n'est pas cochée", function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '0',
    ]);

    $this->assertAuthenticated();
    $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
});

it('pose un cookie « rester connecté » quand la case est cochée', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '1',
    ]);

    $this->assertAuthenticated();
    $response->assertCookie(Auth::guard('web')->getRecallerName());
});

it('ne pose pas de cookie « rester connecté » quand le champ est absent', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
});
