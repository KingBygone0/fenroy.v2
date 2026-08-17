<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_cannot_be_set_via_mass_assignment(): void
    {
        $user = User::create([
            'name'     => 'Attacker',
            'email'    => 'attacker@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) $user->fresh()->is_admin,
            'is_admin must not be settable via mass assignment');
    }

    public function test_registration_cannot_escalate_to_admin(): void
    {
        // Livewire registration — password meets new 12-char+complexity rule
        \Livewire\Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Evil User')
            ->set('email', 'evil@example.com')
            ->set('password', 'EvilPass#12!')
            ->set('password_confirmation', 'EvilPass#12!')
            ->call('register');

        $user = User::where('email', 'evil@example.com')->first();
        $this->assertNotNull($user, 'User should have been created');
        $this->assertFalse((bool) $user->is_admin,
            'is_admin must not be set during registration regardless of any extra inputs');
    }
}
