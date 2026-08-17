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
        $response = $this->post('/register', [
            'name'                  => 'Evil User',
            'email'                 => 'evil@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin'              => true,
        ]);

        $user = User::where('email', 'evil@example.com')->first();

        if ($user) {
            $this->assertFalse((bool) $user->is_admin,
                'Registration must not allow is_admin to be set');
        }
    }
}
