<?php

namespace App\Filament\Resources\PurchaseReport;

use Filament\Pages\Page;
use Filament\Tables;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReport extends Page implements Tables\Contracts\HasTable
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Purchase Report';
    protected static ?string $slug = 'purchase-report';
    protected static ?string $title = 'Purchase Report';

    use Tables\Concerns\InteractsWithTable;

    protected function getTableQuery(): Builder
    {
        return PurchaseItem::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('product.name')
                ->label('Item')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('quantity')
                ->label('Purchased Quantity')
                ->sortable(),

            Tables\Columns\TextColumn::make('sold_quantity')
                ->label('Sold Quantity')
                ->getStateUsing(fn ($record) => $record->sales->sum('quantity')),

            Tables\Columns\TextColumn::make('remaining_quantity')
                ->label('Remaining Quantity')
                ->getStateUsing(fn ($record) => $record->quantity - $record->sales->sum('quantity')),

            Tables\Columns\TextColumn::make('purchase_price')
                ->label('Purchased Price')
                ->money('USD'),

            Tables\Columns\TextColumn::make('sale_price')
                ->label('Selling Price')
                ->money('USD'),

            Tables\Columns\TextColumn::make('profit_per_unit')
                ->label('Profit Per Unit')
                ->getStateUsing(fn ($record) => $record->sale_price - $record->purchase_price)
                ->money('USD'),

            Tables\Columns\TextColumn::make('total_purchase_price')
                ->label('Total Purchase Price')
                ->getStateUsing(fn ($record) => $record->purchase_price * $record->quantity)
                ->money('USD'),

            Tables\Columns\TextColumn::make('total_current_selling_price')
                ->label('Total Current Selling Price')
                ->getStateUsing(fn ($record) => $record->sale_price * ($record->quantity - $record->sales->sum('quantity')))
                ->money('USD'),

            Tables\Columns\TextColumn::make('current_profit')
                ->label('Current Profit')
                ->getStateUsing(fn ($record) => ($record->sale_price - $record->purchase_price) * $record->sales->sum('quantity'))
                ->money('USD'),

            Tables\Columns\TextColumn::make('estimated_profit')
                ->label('Estimated Profit')
                ->getStateUsing(fn ($record) => ($record->sale_price - $record->purchase_price) * $record->quantity)
                ->money('USD'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }
}
