<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('register.ip:127.0.0.1');
    }

    public function test_password_must_be_at_least_12_characters(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'Short1A!')
            ->set('password_confirmation', 'Short1A!')
            ->call('register')
            ->assertHasErrors(['password']);
    }

    public function test_password_must_contain_uppercase_and_lowercase(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'alllowercase123456')
            ->set('password_confirmation', 'alllowercase123456')
            ->call('register')
            ->assertHasErrors(['password']);
    }

    public function test_password_must_contain_a_number(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'NoNumbersHerePls')
            ->set('password_confirmation', 'NoNumbersHerePls')
            ->call('register')
            ->assertHasErrors(['password']);
    }

    public function test_valid_strong_password_is_accepted(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Kofi Mensah')
            ->set('email', 'kofi@example.com')
            ->set('password', 'Secure#Pass12')
            ->set('password_confirmation', 'Secure#Pass12')
            ->call('register')
            ->assertHasNoErrors(['password']);

        $this->assertDatabaseHas('users', ['email' => 'kofi@example.com']);
    }

    public function test_duplicate_email_shows_generic_error_not_enumeration(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $component = Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Attacker')
            ->set('email', 'existing@example.com')
            ->set('password', 'Secure#Pass12')
            ->set('password_confirmation', 'Secure#Pass12')
            ->call('register');

        // Must NOT say "email already taken" or reveal the account exists
        $errors = $component->errors();
        $emailError = $errors->first('email') ?? '';
        $this->assertStringNotContainsString('taken', strtolower($emailError));
        $this->assertStringNotContainsString('already', strtolower($emailError));
        $this->assertEquals('Unable to create account with these details.', $emailError);
    }

    public function test_registration_rate_limit_blocks_after_five_attempts(): void
    {
        $component = Livewire::test(\App\Livewire\Auth\Register::class);

        // Hit the limit
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('register.ip:127.0.0.1', 3600);
        }

        $component->set('name', 'Spammer')
                  ->set('email', 'spam@example.com')
                  ->set('password', 'Secure#Pass12')
                  ->set('password_confirmation', 'Secure#Pass12')
                  ->call('register')
                  ->assertHasErrors(['email']);
    }

    public function test_is_admin_cannot_be_set_during_registration(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Evil Admin')
            ->set('email', 'evil@example.com')
            ->set('password', 'Secure#Pass12')
            ->set('password_confirmation', 'Secure#Pass12')
            ->call('register');

        $user = User::where('email', 'evil@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_name_html_tags_are_stripped(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', '<script>alert(1)</script>Ama')
            ->set('email', 'ama@example.com')
            ->set('password', 'Secure#Pass12')
            ->set('password_confirmation', 'Secure#Pass12')
            ->call('register');

        $user = User::where('email', 'ama@example.com')->first();
        $this->assertNotNull($user);
        $this->assertStringNotContainsString('<script>', $user->name);
    }
}
