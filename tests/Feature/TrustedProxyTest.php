<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * En production, l'application est servie derrière deux proxys — celui de 1Panel,
 * qui termine TLS, puis le nginx de la composition. Sans proxy déclaré, Laravel
 * croit répondre en clair : les liens absolus des e-mails d'invitation partent en
 * http, et `SESSION_SECURE_COOKIE` ne protège plus rien puisque le framework ne
 * se sait pas en HTTPS.
 *
 * La liste des proxys approuvés vient de `config/trustedproxy.php`, lu par le
 * middleware du framework.
 */
beforeEach(function (): void {
    Route::get('/__sonde-proxy', fn () => [
        'secure' => request()->isSecure(),
        'host' => request()->getHost(),
    ]);
});

it('reconstruit le schéma https quand le proxy est approuvé', function (): void {
    config(['trustedproxy.proxies' => '*']);

    $this->get('/__sonde-proxy', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => 'appels.bollirental.africa',
    ])
        ->assertOk()
        ->assertJson([
            'secure' => true,
            'host' => 'appels.bollirental.africa',
        ]);
});

it('ignore les en-têtes de proxy quand aucun proxy n\'est approuvé', function (): void {
    config(['trustedproxy.proxies' => null]);

    // Comportement attendu en développement : n'importe qui peut poser ces
    // en-têtes, les croire sans proxy déclaré serait une usurpation d'origine.
    $this->get('/__sonde-proxy', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => 'attaquant.example',
    ])
        ->assertOk()
        ->assertJson(['secure' => false])
        ->assertJsonMissing(['host' => 'attaquant.example']);
});
