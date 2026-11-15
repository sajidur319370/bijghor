<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->searchable()->copyable()->weight('bold'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn ($record): string => $record->customer_phone)
                    ->searchable(['customer_name', 'customer_phone']),
                TextColumn::make('district')->toggleable(),
                TextColumn::make('total')->prefix('৳ ')->numeric(2)->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => OrderResource::PAYMENT_COLORS[$state] ?? 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => OrderResource::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Placed')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_combine(
                    array_keys(OrderResource::STATUS_COLORS),
                    array_map('ucfirst', array_keys(OrderResource::STATUS_COLORS)),
                )),
                SelectFilter::make('payment_status')->label('Payment')->options(array_combine(
                    array_keys(OrderResource::PAYMENT_COLORS),
                    array_map('ucfirst', array_keys(OrderResource::PAYMENT_COLORS)),
                )),
                Filter::make('placed')
                    ->schema([
                        DatePicker::make('from')->label('Placed from'),
                        DatePicker::make('until')->label('Placed until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make(OrderActions::all()),
            ]);
    }
}
