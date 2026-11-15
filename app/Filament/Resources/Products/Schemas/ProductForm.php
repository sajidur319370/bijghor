<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Descriptions are plain text (not a rich editor): the storefront prints them as text,
 * so there is no HTML to sanitize and no way to inject markup.
 */
class ProductForm
{
    /** Photos: no SVG (it can carry scripts). */
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Basic')->columns(2)->schema([
                            Select::make('category_id')
                                ->relationship('category', 'name_en')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('sku')
                                ->label('SKU')
                                ->maxLength(50)
                                ->unique(ignoreRecord: true),
                            TextInput::make('name_en')
                                ->label('Name (English)')
                                ->required()
                                ->maxLength(200)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('name_bn')
                                ->label('Name (Bangla)')
                                ->required()
                                ->maxLength(200),
                            TextInput::make('slug')
                                ->helperText('Web address: /product/tomato-hybrid-1g. Include the packet size.')
                                ->required()
                                ->maxLength(220)
                                ->alphaDash()
                                ->unique(ignoreRecord: true),
                            TextInput::make('stock')
                                ->required()
                                ->integer()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->prefix('৳'),
                            TextInput::make('sale_price')
                                ->label('Sale price')
                                ->helperText('Leave empty when not on sale. Must be lower than the price.')
                                ->numeric()
                                ->minValue(0)
                                ->lt('price')
                                ->prefix('৳'),
                            Toggle::make('is_active')->label('Shown in the shop')->default(true),
                            Toggle::make('is_featured')->label('Featured on the home page'),
                        ]),

                        Tab::make('Seed info')->columns(2)->schema([
                            TextInput::make('packet_size_en')
                                ->label('Packet size (English)')
                                ->placeholder('10 g or 50 seeds')
                                ->required()
                                ->maxLength(50),
                            TextInput::make('packet_size_bn')
                                ->label('Packet size (Bangla)')
                                ->placeholder('১০ গ্রাম বা ৫০টি বীজ')
                                ->required()
                                ->maxLength(50),
                            TextInput::make('sowing_season_en')
                                ->label('Sowing season (English)')
                                ->placeholder('October - December')
                                ->maxLength(100),
                            TextInput::make('sowing_season_bn')
                                ->label('Sowing season (Bangla)')
                                ->placeholder('অক্টোবর - ডিসেম্বর')
                                ->maxLength(100),
                            TextInput::make('germination_rate')
                                ->label('Germination rate')
                                ->integer()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),
                            TextInput::make('days_to_harvest')
                                ->label('Days to harvest')
                                ->integer()
                                ->minValue(1)
                                ->maxValue(65535),
                        ]),

                        Tab::make('Description')->schema([
                            Textarea::make('description_en')->label('Description (English)')->rows(5)->maxLength(5000),
                            Textarea::make('description_bn')->label('Description (Bangla)')->rows(5)->maxLength(5000),
                        ]),

                        Tab::make('Photos')->schema([
                            FileUpload::make('image')
                                ->label('Main photo')
                                ->disk('uploads')
                                ->directory('products')
                                ->image()
                                ->acceptedFileTypes(self::IMAGE_TYPES)
                                ->maxSize(1024)
                                ->helperText('Square, about 800×800, WebP under 150 KB (compress with squoosh.app).'),
                            Repeater::make('images')
                                ->label('More photos (gallery)')
                                ->relationship()
                                ->orderColumn('sort_order')
                                ->reorderable()
                                ->grid(3)
                                ->addActionLabel('Add photo')
                                ->defaultItems(0)
                                ->schema([
                                    FileUpload::make('path')
                                        ->hiddenLabel()
                                        ->disk('uploads')
                                        ->directory('products')
                                        ->image()
                                        ->acceptedFileTypes(self::IMAGE_TYPES)
                                        ->maxSize(1024)
                                        ->required(),
                                ]),
                        ]),
                    ]),
            ]);
    }
}
