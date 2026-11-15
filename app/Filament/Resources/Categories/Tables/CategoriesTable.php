<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')->disk('uploads')->square(),
                TextColumn::make('name_en')->label('Name (English)')->searchable(),
                TextColumn::make('name_bn')->label('Name (Bangla)')->searchable(),
                TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')->label('Products')->sortable(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_active')->label('Shown')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Shown in the shop'),
            ])
            ->recordActions([
                EditAction::make(),
                // The database refuses to delete a category that still has products (RESTRICT)
                DeleteAction::make()
                    ->disabled(fn (Category $record): bool => $record->products()->withTrashed()->exists())
                    ->tooltip(fn (Category $record): ?string => $record->products()->withTrashed()->exists()
                        ? 'Move or delete its products first'
                        : null),
            ]);
    }
}
