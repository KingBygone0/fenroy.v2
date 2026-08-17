<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_change_requires_current_password(): void
    {
        $user = User::factory()->create([
            'email'    => 'original@example.com',
            'password' => bcrypt('OldPassword12!'),
        ]);

        // Attempt email change WITHOUT providing current password
        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('email', 'new@example.com')
            ->set('current_password', '')
            ->call('save');

        // Email must NOT have changed
        $user->refresh();
        $this->assertEquals('original@example.com', $user->email);
    }

    public function test_email_change_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'email'    => 'original@example.com',
            'password' => bcrypt('OldPassword12!'),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('email', 'stolen@example.com')
            ->set('current_password', 'WrongPassword99!')
            ->call('save');

        $user->refresh();
        $this->assertEquals('original@example.com', $user->email);
    }

    public function test_email_change_with_correct_password_succeeds(): void
    {
        $user = User::factory()->create([
            'email'    => 'original@example.com',
            'password' => bcrypt('OldPassword12!'),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('name', $user->name)
            ->set('email', 'newemail@example.com')
            ->set('current_password', 'OldPassword12!')
            ->call('save');

        $user->refresh();
        $this->assertEquals('newemail@example.com', $user->email);
    }

    public function test_name_change_does_not_require_password(): void
    {
        $user = User::factory()->create(['name' => 'Ama Mensah']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('name', 'Ama Asante')
            ->set('email', $user->email) // same email — no password needed
            ->call('save');

        $user->refresh();
        $this->assertEquals('Ama Asante', $user->name);
    }

    public function test_profile_is_not_accessible_without_auth(): void
    {
        $response = $this->get(route('account.profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_privilege_escalation_via_profile_update_is_blocked(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Try to elevate to admin via profile update
        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->call('save');

        $user->refresh();
        $this->assertFalse((bool) $user->is_admin);
    }
}
