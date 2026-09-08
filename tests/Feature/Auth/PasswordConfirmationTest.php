<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/ConfirmPassword'),
        );
    }

    public function test_password_confirmation_requires_authentication()
    {
        $response = $this->get(route('password.confirm'));

        $response->assertRedirect(route('login'));
    }

    public function test_passkey_confirmation_is_not_offered_without_a_registered_passkey()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/ConfirmPassword')
            ->where('hasPasskeys', false),
        );
    }

    public function test_passkey_confirmation_is_offered_once_a_passkey_is_registered()
    {
        $user = User::factory()->create();

        $user->passkeys()->create([
            'name' => 'MacBook',
            'credential_id' => 'credential-id',
            'credential' => ['id' => 'credential-id'],
        ]);

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/ConfirmPassword')
            ->where('hasPasskeys', true),
        );
    }
}
