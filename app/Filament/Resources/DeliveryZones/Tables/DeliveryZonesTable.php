<?php

namespace App\Filament\Resources\DeliveryZones\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DeliveryZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name_en')->label('Name (English)'),
                TextColumn::make('name_bn')->label('Name (Bangla)'),
                TextColumn::make('charge')->prefix('৳ ')->numeric(2)->sortable(),
                ToggleColumn::make('is_active')->label('Offered'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),   // orders keep their saved charge (delivery_zone_id → NULL)
            ]);
    }
}
