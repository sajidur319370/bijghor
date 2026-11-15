<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\LowStock;
use App\Filament\Widgets\NeedsRefund;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_stats_show_todays_orders_sales_and_orders_waiting_to_ship(): void
    {
        Order::factory()->paid()->create(['total' => '458.00', 'subtotal' => '388.00']);
        Order::factory()->paid()->create(['total' => '200.00', 'subtotal' => '130.00']);
        Order::factory()->create();                                                                 // unpaid: not a sale
        Order::factory()->create(['status' => 'cancelled', 'payment_status' => 'paid', 'paid_at' => now()]);   // being refunded: not a sale

        Livewire::test(StatsOverview::class)
            ->assertSee("Today's orders")
            ->assertSee('৳ 658')
            ->assertSee('Waiting to ship')
            ->assertSee('Needs refund');
    }

    public function test_sales_chart_has_30_days_and_counts_paid_orders_only(): void
    {
        Order::factory()->paid()->create(['total' => '458.00']);
        $this->travel(-3)->days();
        Order::factory()->paid()->create(['total' => '200.00', 'subtotal' => '130.00']);
        $this->travelBack();

        $widget = Livewire::test(SalesChart::class)->instance();
        $data = (fn () => $this->getData())->call($widget);
        $values = $data['datasets'][0]['data'];

        $this->assertCount(30, $values);
        $this->assertSame(458.0, end($values));
        $this->assertSame(200.0, $values[26]);
        $this->assertSame(658.0, array_sum($values));
    }

    public function test_low_stock_lists_active_products_with_ten_or_fewer(): void
    {
        $low = Product::factory()->create(['stock' => 3]);
        $plenty = Product::factory()->create(['stock' => 50]);
        $hidden = Product::factory()->inactive()->create(['stock' => 0]);

        Livewire::test(LowStock::class)
            ->assertCanSeeTableRecords([$low])
            ->assertCanNotSeeTableRecords([$plenty, $hidden]);
    }

    public function test_needs_refund_appears_only_when_needed_and_can_mark_refunded(): void
    {
        $this->assertFalse(NeedsRefund::canView());

        $order = Order::factory()->create(['status' => 'cancelled', 'payment_status' => 'paid', 'paid_at' => now()]);
        $this->assertTrue(NeedsRefund::canView());

        Livewire::test(NeedsRefund::class)
            ->assertCanSeeTableRecords([$order])
            ->callAction(TestAction::make('markRefunded')->table($order));

        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertFalse(NeedsRefund::canView());
    }

    public function test_dashboard_page_shows_the_widgets(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSeeLivewire(StatsOverview::class)
            ->assertSeeLivewire(SalesChart::class)
            ->assertSeeLivewire(LowStock::class)
            ->assertDontSeeLivewire(NeedsRefund::class);   // nothing to refund
    }
}
