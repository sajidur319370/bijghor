<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Order')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('order_number')->copyable()->weight('bold'),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => OrderResource::STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('payment_status')->label('Payment')->badge()->color(fn (string $state): string => OrderResource::PAYMENT_COLORS[$state] ?? 'gray'),
                        TextEntry::make('refund_note')
                            ->hiddenLabel()
                            ->state('Paid after the order was cancelled: refund the customer, then use "Mark refunded".')
                            ->color('danger')
                            ->visible(fn (Order $record): bool => $record->needsRefund()),
                        TextEntry::make('created_at')->label('Placed')->dateTime('d M Y, h:i A'),
                        TextEntry::make('paid_at')->label('Paid')->dateTime('d M Y, h:i A')->placeholder('—'),
                        TextEntry::make('locale')->label('Customer language')->formatStateUsing(fn (string $state): string => $state === 'bn' ? 'Bangla' : 'English'),
                    ]),

                Section::make('Customer and delivery')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer_name')->label('Name'),
                        TextEntry::make('customer_phone')->label('Mobile')->copyable(),
                        TextEntry::make('customer_email')->label('Email')->placeholder('—'),
                        TextEntry::make('deliveryZone.name_en')->label('Delivery area')->placeholder('(zone deleted)'),
                        TextEntry::make('district'),
                        TextEntry::make('address')->columnSpanFull(),
                        TextEntry::make('note')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->table([
                                RepeatableEntry\TableColumn::make('Product'),
                                RepeatableEntry\TableColumn::make('Packet'),
                                RepeatableEntry\TableColumn::make('Price'),
                                RepeatableEntry\TableColumn::make('Qty'),
                                RepeatableEntry\TableColumn::make('Line total'),
                            ])
                            ->schema([
                                TextEntry::make('product_name_en')
                                    ->formatStateUsing(fn (string $state, $record): string => $state.' · '.$record->product_name_bn),
                                TextEntry::make('packet_size_en'),
                                TextEntry::make('unit_price')->prefix('৳ ')->numeric(2),
                                TextEntry::make('quantity'),
                                TextEntry::make('line_total')->prefix('৳ ')->numeric(2),
                            ]),
                        TextEntry::make('subtotal')->prefix('৳ ')->numeric(2)->inlineLabel(),
                        TextEntry::make('delivery_charge')->prefix('৳ ')->numeric(2)->inlineLabel(),
                        TextEntry::make('total')->prefix('৳ ')->numeric(2)->weight('bold')->inlineLabel(),
                    ]),

                Section::make('Payment attempts')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->hiddenLabel()
                            ->placeholder('No payment attempts yet')
                            ->table([
                                RepeatableEntry\TableColumn::make('Started'),
                                RepeatableEntry\TableColumn::make('Payment ID'),
                                RepeatableEntry\TableColumn::make('Transaction ID'),
                                RepeatableEntry\TableColumn::make('Amount'),
                                RepeatableEntry\TableColumn::make('Status'),
                            ])
                            ->schema([
                                TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
                                TextEntry::make('bkash_payment_id')->copyable(),
                                TextEntry::make('trx_id')->placeholder('—')->copyable(),
                                TextEntry::make('amount')->prefix('৳ ')->numeric(2),
                                TextEntry::make('status')->badge(),
                            ]),
                    ]),
            ]);
    }
}
