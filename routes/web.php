<?php

use App\Http\Controllers\AgentController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Créer un accès est une action sensible : elle exige une reconfirmation du mot
    // de passe, comme les réglages de sécurité.
    Route::middleware(RequirePassword::class)->group(function () {
        Route::get('agents', [AgentController::class, 'index'])->name('agents.index');

        Route::post('agents', [AgentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('agents.store');

        Route::post('agents/{agent}/invitation', [AgentController::class, 'resendInvitation'])
            ->middleware('throttle:6,1')
            ->name('agents.invitation.resend');
    });
});

require __DIR__.'/settings.php';
