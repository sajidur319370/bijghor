<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Products are only soft-deleted (no force delete), so order history keeps its link to them.
 */
class ProductsTable
{
    public const LOW_STOCK = 10;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image')->disk('uploads')->square(),
                TextColumn::make('name_en')
                    ->label('Name')
                    ->description(fn ($record): string => $record->name_bn.' · '.$record->packet_size_en)
                    ->searchable(['name_en', 'name_bn', 'sku']),
                TextColumn::make('category.name_en')->label('Category')->sortable(),
                TextColumn::make('price')->prefix('৳ ')->numeric(2)->sortable(),
                TextColumn::make('sale_price')->label('Sale')->prefix('৳ ')->numeric(2)->placeholder('—')->sortable(),
                TextColumn::make('stock')
                    ->sortable()
                    ->color(fn (int $state): ?string => $state <= self::LOW_STOCK ? 'danger' : null)
                    ->weight(fn (int $state): ?string => $state <= self::LOW_STOCK ? 'bold' : null),
                ToggleColumn::make('is_active')->label('Shown'),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('sku')->label('SKU')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name_en'),
                TernaryFilter::make('is_active')->label('Shown in the shop'),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filter::make('low_stock')
                    ->label('Low stock (10 or less)')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', self::LOW_STOCK)),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
