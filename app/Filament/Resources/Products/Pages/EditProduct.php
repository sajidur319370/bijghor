<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View in shop')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('product', $this->record->slug), shouldOpenInNewTab: true)
                ->visible(fn (): bool => $this->record->is_active && ! $this->record->trashed()),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
