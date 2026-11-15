<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderActions;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Orders paid after auto-cancel had already returned their stock (docs §5.8, §5.11).
 * Only shown when there is at least one.
 */
class NeedsRefund extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return self::query()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Needs refund')
            ->description('These customers paid after their order was cancelled. Refund them, then mark the order refunded.')
            ->query(fn (): Builder => self::query()->orderBy('paid_at'))
            ->paginated(false)
            ->columns([
                TextColumn::make('order_number')->label('Order')->weight('bold'),
                TextColumn::make('customer_name')->label('Customer')->description(fn (Order $record): string => $record->customer_phone),
                TextColumn::make('total')->prefix('৳ ')->numeric(2),
                TextColumn::make('paid_at')->label('Paid')->dateTime('d M Y, h:i A'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Open')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                OrderActions::markRefunded(),
            ]);
    }

    private static function query(): Builder
    {
        return Order::query()->where('status', 'cancelled')->where('payment_status', 'paid');
    }
}
