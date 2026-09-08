<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('calls', CallController::class);

    Route::resource('clients', ClientController::class)->only(['index', 'show']);
    Route::resource('reservations', ReservationController::class)->only(['index', 'show']);

    // Créer un accès est une action sensible : elle exige une reconfirmation du mot
    // de passe, comme les réglages de sécurité.
    Route::middleware(RequirePassword::class)->group(function () {
        Route::get('agents', [AgentController::class, 'index'])->name('agents.index');

        Route::post('agents', [AgentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('agents.store');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/api.php';
