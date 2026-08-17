<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sessionId = session()->getId();

        $this->post(route('logout'));

        // After logout, the old session ID should no longer be valid
        $this->assertGuest();
    }

    public function test_protected_account_routes_require_authentication(): void
    {
        $routes = [
            route('account.profile'),
            route('account.orders'),
            route('account.wishlist'),
            route('account.addresses'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_order_tracking_requires_authentication(): void
    {
        $response = $this->get(route('order.track', ['orderNumber' => 'FEN-ABC123']));
        $response->assertRedirect(route('login'));
    }

    public function test_logout_requires_post_not_get(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // GET logout should not work
        $response = $this->get('/logout');
        $response->assertStatus(405); // Method not allowed

        // User should still be authenticated
        $this->assertAuthenticatedAs($user);
    }

    public function test_unauthenticated_user_cannot_access_admin_panel(): void
    {
        $response = $this->get('/store-portal');
        // Filament redirects unauthenticated users — must not return 200
        $response->assertRedirect();
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get('/store-portal');
        // Filament redirects non-admins — should not get a 200 with panel content
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}
