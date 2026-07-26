<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Product Details')->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                TextInput::make('origin')->placeholder('e.g. Pattamadai village, Tamil Nadu'),
                TextInput::make('material'),
                TextInput::make('dimensions'),
                TextInput::make('craft_time'),
            ])->columns(2),

            Section::make('Pricing & Stock')->schema([
                TextInput::make('price_in_paise')
                    ->label('Base Price (₹)')
                    ->required()
                    ->numeric()
                    ->prefix('₹')
                    // Stored as paise, but admins think in rupees — convert both ways.
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                    ->dehydrateStateUsing(fn ($state) => (int) round($state * 100)),
                TextInput::make('stock_quantity')
                    ->label('Stock (used only if this product has no variants)')
                    ->required()
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Section::make('Visibility')->schema([
                Toggle::make('is_active')->label('Published (visible on storefront)')->default(true),
                Toggle::make('is_featured')->label('Featured on homepage'),
            ])->columns(2),

            Section::make('SEO')->schema([
                TextInput::make('meta_title'),
                Textarea::make('meta_description')->rows(2),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('price_in_paise')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => '₹' . number_format($state / 100, 2))
                    ->sortable(),
                TextColumn::make('stock_quantity')->label('Stock')->sortable(),
                IconColumn::make('is_active')->label('Live')->boolean(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_active')->label('Published'),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
