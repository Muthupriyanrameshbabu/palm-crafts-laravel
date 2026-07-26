<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    // Read-only line items — see OrderResource for why order data is immutable.
    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('product_name'),
            TextColumn::make('variant_name'),
            TextColumn::make('quantity'),
            TextColumn::make('unit_price_in_paise')
                ->label('Unit Price')
                ->formatStateUsing(fn ($state) => '₹' . number_format($state / 100, 2)),
            TextColumn::make('line_total_in_paise')
                ->label('Line Total')
                ->formatStateUsing(fn ($state) => '₹' . number_format($state / 100, 2)),
        ]);
    }
}
