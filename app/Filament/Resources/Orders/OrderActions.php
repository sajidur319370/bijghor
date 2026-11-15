<?php

namespace App\Filament\Resources\Orders;

use App\Actions\CancelOrder;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Order status actions, shared by the orders table and the order page.
 */
class OrderActions
{
    /**
     * @return list<Action>
     */
    public static function all(): array
    {
        return [self::markShipped(), self::markDelivered(), self::markRefunded(), self::cancel()];
    }

    public static function markShipped(): Action
    {
        return Action::make('markShipped')
            ->label('Mark shipped')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->visible(fn (Order $record): bool => $record->status === 'confirmed')
            ->requiresConfirmation()
            ->action(fn (Order $record) => self::notify($record->markShipped(), 'Marked as shipped'));
    }

    public static function markDelivered(): Action
    {
        return Action::make('markDelivered')
            ->label('Mark delivered')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (Order $record): bool => $record->status === 'shipped')
            ->requiresConfirmation()
            ->action(fn (Order $record) => self::notify($record->markDelivered(), 'Marked as delivered'));
    }

    public static function markRefunded(): Action
    {
        return Action::make('markRefunded')
            ->label('Mark refunded')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (Order $record): bool => $record->needsRefund())
            ->requiresConfirmation()
            ->modalDescription('Do this after returning the money to the customer. (Demo/sandbox: no real money moved.)')
            ->action(fn (Order $record) => self::notify($record->markRefunded(), 'Marked as refunded'));
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Cancel order')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Order $record): bool => in_array($record->status, CancelOrder::CANCELLABLE_STATUSES, true))
            ->requiresConfirmation()
            ->modalHeading('Cancel this order?')
            ->modalDescription(fn (Order $record): string => $record->payment_status === 'paid'
                ? 'The stock goes back, and the order and payment are marked refunded.'
                : 'The stock goes back. This cannot be undone.')
            ->action(function (Order $record, CancelOrder $cancelOrder): void {
                self::notify($cancelOrder->handle($record), 'Order cancelled, stock restored');
                $record->refresh();
            });
    }

    private static function notify(bool $changed, string $success): void
    {
        $changed
            ? Notification::make()->title($success)->success()->send()
            : Notification::make()->title('The order changed meanwhile. Reload and try again.')->warning()->send();
    }
}
