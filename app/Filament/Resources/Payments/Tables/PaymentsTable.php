<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Actions\SettlePayment;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public const STATUS_COLORS = [
        'initiated' => 'warning',
        'completed' => 'success',
        'failed' => 'danger',
        'cancelled' => 'gray',
        'refunded' => 'info',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('order'))
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->url(fn (Payment $record): string => OrderResource::getUrl('view', ['record' => $record->order])),
                TextColumn::make('trx_id')->label('Transaction ID')->placeholder('—')->searchable()->copyable(),
                TextColumn::make('bkash_payment_id')->label('Payment ID')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payer_account')->label('Payer')->placeholder('—'),
                TextColumn::make('amount')->prefix('৳ ')->numeric(2)->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Started')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('completed_at')->label('Completed')->dateTime('d M Y, h:i A')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_combine(
                    array_keys(self::STATUS_COLORS),
                    array_map('ucfirst', array_keys(self::STATUS_COLORS)),
                )),
            ])
            ->recordActions([
                self::checkWithBkash(),
            ]);
    }

    /**
     * For attempts with no clear result (customer left, or bKash timed out): ask bKash for the
     * real status and apply the same rules as the callback (docs §5.8, §5.11).
     */
    public static function checkWithBkash(): Action
    {
        return Action::make('checkWithBkash')
            ->label('Check with bKash')
            ->icon('heroicon-o-arrow-path')
            ->visible(fn (Payment $record): bool => $record->status === 'initiated')
            ->action(function (Payment $record, SettlePayment $settle): void {
                $result = $settle->handle($record, execute: false);

                match ($result) {
                    'completed' => Notification::make()->title('Paid: the payment is now completed')->success()->send(),
                    'failed' => Notification::make()->title('Not paid: the attempt is marked failed')->warning()->send(),
                    default => Notification::make()->title('bKash gave no clear answer. Try again later.')->danger()->send(),
                };
            });
    }
}
