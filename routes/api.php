<?php

use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\V1\CallController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
 * Ce fichier est inclus depuis routes/web.php et hérite donc du groupe `web` :
 * l'API est authentifiée par la session de l'application, sans jeton.
 *
 * C'est un choix assumé pour ce périmètre. Ouvrir l'écriture à un client mobile
 * suppose une authentification par jeton (Laravel Sanctum) ; en lecture seule,
 * la session couvre le besoin annoncé — « exposer les appels » — et évite d'avoir
 * à protéger des routes d'écriture contre le CSRF.
 */

Route::middleware('auth')->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::get('calls', [CallController::class, 'index'])->name('calls.index');
    Route::get('calls/{call}', [CallController::class, 'show'])->name('calls.show');

    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');

    Route::get('statistics', StatisticsController::class)->name('statistics');
});

/*
 * L'interface Swagger vit hors du préfixe `api/` : `bootstrap/app.php` rend toute
 * erreur sous `api/*` en JSON, et un humain non connecté doit être redirigé vers
 * la connexion, pas recevoir un 401 brut. Le document lui-même reste sous `api/`,
 * puisqu'il s'adresse à des machines.
 */
Route::middleware('auth')->group(function () {
    Route::get('docs/api', [DocumentationController::class, 'index'])->name('api.documentation');
    Route::get('api/documentation.json', [DocumentationController::class, 'schema'])->name('api.documentation.schema');
});
