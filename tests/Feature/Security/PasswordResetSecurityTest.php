<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('password.reset:127.0.0.1');
        RateLimiter::clear('pw.reset:127.0.0.1');
    }

    public function test_forgot_password_shows_same_message_for_existing_and_nonexistent_emails(): void
    {
        User::factory()->create(['email' => 'real@example.com']);

        $componentReal = Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'real@example.com')
            ->call('send');

        $componentFake = Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'fake@example.com')
            ->call('send');

        $this->assertEquals(
            $componentReal->get('status'),
            $componentFake->get('status'),
            'Status message must be identical regardless of whether the email exists'
        );
    }

    public function test_forgot_password_rate_limit(): void
    {
        // Exhaust the 3-attempt limit
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit('password.reset:127.0.0.1', 3600);
        }

        $component = Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'anyone@example.com')
            ->call('send');

        $this->assertNotEmpty($component->get('error'));
        $this->assertStringContainsString('Too many', $component->get('error'));
    }

    public function test_reset_password_shows_generic_error_for_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $component = Livewire::test(
            \App\Livewire\Auth\ResetPassword::class,
            ['token' => 'totally-invalid-token']
        )
            ->set('email', 'user@example.com')
            ->set('password', 'NewSecurePass12')
            ->set('password_confirmation', 'NewSecurePass12')
            ->call('savePassword');

        $error = $component->get('error');
        $this->assertStringNotContainsString('find an account', $error,
            'Error must not reveal whether the email exists');
        $this->assertNotEmpty($error);
    }

    public function test_reset_password_shows_generic_error_for_unknown_email(): void
    {
        $component = Livewire::test(
            \App\Livewire\Auth\ResetPassword::class,
            ['token' => 'some-token']
        )
            ->set('email', 'nobody@example.com')
            ->set('password', 'NewSecurePass12')
            ->set('password_confirmation', 'NewSecurePass12')
            ->call('savePassword');

        $error = $component->get('error');
        $this->assertStringNotContainsString('find an account', $error,
            'Error must not reveal whether the email exists');
    }

    public function test_reset_password_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('pw.reset:127.0.0.1', 900);
        }

        $component = Livewire::test(
            \App\Livewire\Auth\ResetPassword::class,
            ['token' => 'x']
        )
            ->set('email', 'x@example.com')
            ->set('password', 'NewSecurePass12')
            ->set('password_confirmation', 'NewSecurePass12')
            ->call('savePassword');

        $error = $component->get('error');
        $this->assertStringContainsString('Too many', $error);
    }

    public function test_password_reset_kills_all_existing_sessions(): void
    {
        $user = User::factory()->create(['email' => 'session-victim@example.com']);

        // Simulate two existing sessions for this user
        DB::table('sessions')->insert([
            ['id' => 'sess-a', 'user_id' => $user->id, 'ip_address' => '1.1.1.1', 'user_agent' => 'ua', 'payload' => 'x', 'last_activity' => now()->timestamp],
            ['id' => 'sess-b', 'user_id' => $user->id, 'ip_address' => '2.2.2.2', 'user_agent' => 'ua', 'payload' => 'x', 'last_activity' => now()->timestamp],
        ]);

        $token = Password::createToken($user);

        Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => $token])
            ->set('email', 'session-victim@example.com')
            ->set('password', 'BrandNew#Pass12')
            ->set('password_confirmation', 'BrandNew#Pass12')
            ->call('savePassword');

        $remaining = DB::table('sessions')->where('user_id', $user->id)->count();
        $this->assertEquals(0, $remaining, 'All sessions must be destroyed after password reset');
    }

    public function test_reset_token_is_consumed_after_use(): void
    {
        $user  = User::factory()->create(['email' => 'token-user@example.com']);
        $token = Password::createToken($user);

        // First use — should succeed
        Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => $token])
            ->set('email', 'token-user@example.com')
            ->set('password', 'FirstNew#Pass12')
            ->set('password_confirmation', 'FirstNew#Pass12')
            ->call('savePassword');

        // Second use of same token — should fail (token is consumed)
        $component = Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => $token])
            ->set('email', 'token-user@example.com')
            ->set('password', 'SecondNew#Pass12')
            ->set('password_confirmation', 'SecondNew#Pass12')
            ->call('savePassword');

        $this->assertNotEmpty($component->get('error'),
            'Reusing a spent reset token must fail');
    }

    public function test_reset_enforces_password_complexity(): void
    {
        $user  = User::factory()->create(['email' => 'weak-reset@example.com']);
        $token = Password::createToken($user);

        $component = Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => $token])
            ->set('email', 'weak-reset@example.com')
            ->set('password', 'tooshort')
            ->set('password_confirmation', 'tooshort')
            ->call('savePassword');

        $component->assertHasErrors(['password']);
    }
}
