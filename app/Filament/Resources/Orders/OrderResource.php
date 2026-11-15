<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * View-only: orders are created by checkout, never typed in by hand.
 * Staff change them only through the actions in OrderActions.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
    ];

    public const PAYMENT_COLORS = [
        'unpaid' => 'gray',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'warning',
    ];

    public static function canCreate(): bool
    {
        return false;
    }

    /** Badge in the menu: paid orders waiting to be shipped. */
    public static function getNavigationBadge(): ?string
    {
        $waiting = Order::where('status', 'confirmed')->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Paid orders waiting to ship';
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
