<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\DeliveryZones\Pages\CreateDeliveryZone;
use App\Filament\Resources\DeliveryZones\Pages\EditDeliveryZone;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryZoneResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_a_new_zone_is_offered_at_checkout(): void
    {
        Livewire::test(CreateDeliveryZone::class)
            ->fillForm(['name_en' => 'Chattogram City', 'name_bn' => 'চট্টগ্রাম শহর', 'charge' => 100])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::factory()->create();
        $this->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout'))
            ->assertSee('চট্টগ্রাম শহর')
            ->assertSee('৳ ১০০');
    }

    public function test_charge_cannot_be_negative(): void
    {
        Livewire::test(CreateDeliveryZone::class)
            ->fillForm(['name_en' => 'X', 'name_bn' => 'X', 'charge' => -5])
            ->call('create')
            ->assertHasFormErrors(['charge']);
    }

    public function test_changing_the_charge_does_not_change_old_orders(): void
    {
        $zone = DeliveryZone::create(['name_en' => 'Inside Dhaka', 'name_bn' => 'ঢাকার ভিতরে', 'charge' => 70]);
        $order = Order::factory()->create(['delivery_zone_id' => $zone->id]);

        Livewire::test(EditDeliveryZone::class, ['record' => $zone->getRouteKey()])
            ->fillForm(['charge' => 90])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('90.00', $zone->fresh()->charge);
        $this->assertSame('70.00', $order->fresh()->delivery_charge);
    }
}
