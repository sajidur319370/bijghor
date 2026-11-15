<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_en')
                            ->label('Name (English)')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('name_bn')
                            ->label('Name (Bangla)')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('slug')
                            ->helperText('Used in the web address: /category/vegetable-seeds')
                            ->required()
                            ->maxLength(140)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->helperText('Smaller numbers come first.')
                            ->required()
                            ->integer()
                            ->default(0),
                        FileUpload::make('image')
                            ->disk('uploads')
                            ->directory('categories')
                            ->image()
                            ->maxSize(1024)
                            ->helperText('Square image, WebP under 150 KB is best.'),
                        Toggle::make('is_active')
                            ->label('Shown in the shop')
                            ->default(true),
                    ]),
            ]);
    }
}
