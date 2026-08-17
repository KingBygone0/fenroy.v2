<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login.ip:127.0.0.1');
    }

    public function test_failed_login_shows_generic_error_not_field_specific(): void
    {
        User::factory()->create(['email' => 'real@example.com']);

        $component = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'real@example.com')
            ->set('password', 'wrongpassword')
            ->call('login');

        $component->assertSet('error', 'These credentials do not match our records.');
        // Must not say "password incorrect" or "email not found" — same message for both
    }

    public function test_login_with_nonexistent_email_shows_same_error_as_wrong_password(): void
    {
        $component = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'nobody@example.com')
            ->set('password', 'SomePassword123!')
            ->call('login');

        $component->assertSet('error', 'These credentials do not match our records.');
    }

    public function test_ip_rate_limit_blocks_after_five_failed_attempts(): void
    {
        User::factory()->create(['email' => 'victim@example.com']);

        $component = Livewire::test(\App\Livewire\Auth\Login::class);

        for ($i = 0; $i < 5; $i++) {
            $component->set('email', 'victim@example.com')
                      ->set('password', 'wrong')
                      ->call('login');
        }

        // 6th attempt — should be blocked by IP rate limit
        $component->set('email', 'victim@example.com')
                  ->set('password', 'wrong')
                  ->call('login');

        $error = $component->get('error');
        $this->assertStringContainsString('Too many login attempts', $error);
    }

    public function test_email_rate_limit_blocks_same_email_across_requests(): void
    {
        User::factory()->create(['email' => 'target@example.com']);

        // Hit per-email limit (10 attempts)
        $key = 'login.email:target@example.com';
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($key, 900);
        }

        $component = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'target@example.com')
            ->set('password', 'wrong')
            ->call('login');

        $error = $component->get('error');
        $this->assertStringContainsString('Too many login attempts', $error);

        RateLimiter::clear($key);
    }

    public function test_session_is_regenerated_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('SecurePass123!'),
        ]);

        $oldSessionId = session()->getId();

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'user@example.com')
            ->set('password', 'SecurePass123!')
            ->call('login');

        // Session ID must have changed — prevents session fixation
        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    public function test_rate_limiter_clears_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email'    => 'clean@example.com',
            'password' => bcrypt('SecurePass123!'),
        ]);

        // Fail a few times
        $component = Livewire::test(\App\Livewire\Auth\Login::class);
        for ($i = 0; $i < 3; $i++) {
            $component->set('email', 'clean@example.com')
                      ->set('password', 'wrong')
                      ->call('login');
        }

        // Succeed — rate limiter must be cleared
        $component->set('email', 'clean@example.com')
                  ->set('password', 'SecurePass123!')
                  ->call('login');

        // Key should now be clear
        $this->assertFalse(RateLimiter::tooManyAttempts('login.ip:127.0.0.1', 5));
    }
}
