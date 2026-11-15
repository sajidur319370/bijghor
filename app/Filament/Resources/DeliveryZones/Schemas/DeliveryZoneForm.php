<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_en')->label('Name (English)')->placeholder('Inside Dhaka')->required()->maxLength(100),
                        TextInput::make('name_bn')->label('Name (Bangla)')->placeholder('ঢাকার ভিতরে')->required()->maxLength(100),
                        TextInput::make('charge')
                            ->label('Delivery charge')
                            ->helperText('Changing it only affects new orders; old orders keep the charge they paid.')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->default(0),
                        TextInput::make('sort_order')->label('Sort order')->required()->integer()->default(0),
                        Toggle::make('is_active')
                            ->label('Offered at checkout')
                            ->helperText('Switch off instead of deleting a zone you no longer deliver to.')
                            ->default(true),
                    ]),
            ]);
    }
}
