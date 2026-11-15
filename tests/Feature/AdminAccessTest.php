<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_a_logged_in_user_who_is_not_an_admin_cannot_open_the_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_an_admin_can_open_the_dashboard_in_english(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('BijGhor Admin')
            ->assertSee('lang="en"', false)
            ->assertDontSee('ui-avatars.com', false);
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::create(['name' => 'X', 'email' => 'x@example.com', 'password' => 'secret-password', 'is_admin' => true]);

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_opening_the_dashboard_cancels_stale_unpaid_orders(): void
    {
        $this->travel(-2)->hours();
        $stale = Order::factory()->create();
        $this->travelBack();

        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();

        $this->assertSame('cancelled', $stale->fresh()->status);
    }
}
