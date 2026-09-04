<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\AgentInvitation;
use Illuminate\Support\Facades\Notification;

/**
 * Confirme le mot de passe dans la session : la zone « agents » est protégée par
 * le middleware RequirePassword, comme les réglages de sécurité.
 */
function actingAsConfirmedAgent(): User
{
    $agent = User::factory()->create();

    test()->actingAs($agent)->withSession(['auth.password_confirmed_at' => time()]);

    return $agent;
}

it('redirige un visiteur anonyme vers la connexion', function (): void {
    $this->get(route('agents.index'))->assertRedirect(route('login'));
});

it('exige une reconfirmation du mot de passe avant de montrer les agents', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('agents.index'))
        ->assertRedirect(route('password.confirm'));
});

it('liste les agents avec leur volume d\'appels traité', function (): void {
    $agent = actingAsConfirmedAgent();

    $this->get(route('agents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('agents/Index')
            ->has('agents', 1)
            ->where('agents.0.email', $agent->email)
            ->where('agents.0.calls_count', 0)
            ->where('agents.0.is_current', true)
        );
});

it('crée l\'agent et lui envoie ses accès par e-mail', function (): void {
    Notification::fake();
    $inviter = actingAsConfirmedAgent();

    $this->post(route('agents.store'), [
        'name' => 'Aya Touré',
        'email' => 'aya.toure@bollirental.africa',
    ])->assertRedirect(route('agents.index'));

    $invited = User::query()->where('email', 'aya.toure@bollirental.africa')->sole();

    expect($invited->name)->toBe('Aya Touré');

    Notification::assertSentTo($invited, AgentInvitation::class);
    expect($inviter->fresh()->email)->toBe($inviter->email);
});

it('ne transmet jamais un mot de passe utilisable dans l\'invitation', function (): void {
    actingAsConfirmedAgent();

    $this->post(route('agents.store'), [
        'name' => 'Aya Touré',
        'email' => 'aya.toure@bollirental.africa',
    ]);

    $invited = User::query()->where('email', 'aya.toure@bollirental.africa')->sole();

    $this->post(route('logout'));

    // Le compte existe mais reste inutilisable tant que le lien n'a pas servi.
    $this->post(route('login.store'), [
        'email' => $invited->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

it('refuse une adresse déjà utilisée par un agent', function (): void {
    $existing = actingAsConfirmedAgent();

    $this->post(route('agents.store'), [
        'name' => 'Doublon',
        'email' => $existing->email,
    ])->assertSessionHasErrors('email');

    expect(User::query()->count())->toBe(1);
});

it('refuse une invitation sans nom ni adresse valide', function (): void {
    actingAsConfirmedAgent();

    $this->post(route('agents.store'), [
        'name' => '',
        'email' => 'pas-une-adresse',
    ])->assertSessionHasErrors(['name', 'email']);
});

it('renvoie une invitation à un agent existant', function (): void {
    Notification::fake();
    actingAsConfirmedAgent();

    $agent = User::factory()->create();

    $this->post(route('agents.invitation.resend', $agent))->assertRedirect();

    Notification::assertSentTo($agent, AgentInvitation::class);
});

it("permet à l'agent invité de définir son mot de passe via le lien reçu", function (): void {
    Notification::fake();
    actingAsConfirmedAgent();

    $this->post(route('agents.store'), [
        'name' => 'Aya Touré',
        'email' => 'aya.toure@bollirental.africa',
    ]);

    $invited = User::query()->where('email', 'aya.toure@bollirental.africa')->sole();

    $token = null;
    Notification::assertSentTo($invited, AgentInvitation::class, function (AgentInvitation $notification) use (&$token, $invited): bool {
        $token = (new ReflectionProperty($notification, 'token'))->getValue($notification);

        return str_contains($notification->toMail($invited)->actionUrl, $token);
    });

    // Le lien est destiné à l'invité, pas à l'agent qui l'a créé : la route de
    // réinitialisation est réservée aux visiteurs anonymes.
    $this->post(route('logout'));

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $invited->email,
        'password' => 'Bolli@Agent2026!',
        'password_confirmation' => 'Bolli@Agent2026!',
    ])->assertSessionHasNoErrors();

    $this->post(route('logout'));

    $this->post(route('login.store'), [
        'email' => $invited->email,
        'password' => 'Bolli@Agent2026!',
    ]);

    $this->assertAuthenticatedAs($invited->fresh());
});
