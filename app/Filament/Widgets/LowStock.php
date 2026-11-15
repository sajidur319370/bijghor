<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Products for sale with 10 or fewer packets left (docs §5.11).
 */
class LowStock extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low stock')
            ->query(fn (): Builder => Product::active()->where('stock', '<=', ProductsTable::LOW_STOCK)->orderBy('stock'))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Every product has more than 10 packets')
            ->columns([
                TextColumn::make('name_en')->label('Product')->description(fn (Product $record): string => $record->name_bn),
                TextColumn::make('packet_size_en')->label('Packet'),
                TextColumn::make('sku')->label('SKU')->placeholder('—'),
                TextColumn::make('stock')->badge()->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
