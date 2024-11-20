<?php

namespace App\Filament\Pages;

use App\Models\PurchaseItem;
use App\Models\SalesItem;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PurchaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    public $name = 'Purchase Report';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.purchase-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseItem::query())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('expiry_date')->sortable(),

                // Purchase Quantity (Remaining Quantity + Sold Quantity)
                TextColumn::make('purchase_quantity')
                    ->label('Purchase Quantity')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return $this->getSoldQuantity($record->id) + ($record->quantity);
                    }),

                // Purchase Unit Price
                TextColumn::make('purchase_price')
                    ->label('Purchase Unit Price'),

                // Purchase Total Price (Purchase Quantity * Purchase Price)
                TextColumn::make('purchase_total_price')
                    ->label('Purchase Total Price')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return ($record->quantity + $this->getSoldQuantity($record->id) )* $record->purchase_price;
                    }),

                // Sales Quantity
                TextColumn::make('sales_quantity')
                    ->label('Sales Quantity')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return $this->getSoldQuantity($record->id);
                    }),

                // Sales Unit Price (If different for the same purchase, else use purchase sale_price)
                TextColumn::make('sales_unit_price')
                    ->label('Sales Unit Price')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return $this->getSalesPriceOrDefault($record);
                    }),

                // Sales Total Price (Sales Quantity * Sales Unit Price)
                TextColumn::make('sales_total_price')
                    ->label('Sales Total Price')
                    ->getStateUsing(function (PurchaseItem $record) {
                        $salesQuantity = $this->getSoldQuantity($record->id);
                        $salesUnitPrice = $this->getSalesPriceOrDefault($record);
                        return $salesQuantity * $salesUnitPrice;
                    }),

                // Remaining Quantity (Purchase Quantity - Sales Quantity)
                TextColumn::make('remaining_quantity')
                    ->label('Remaining Quantity')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return $record->quantity ;
                    }),

                // Price (Last Sale Price or Default to Purchase Sale Price)
                TextColumn::make('price')
                    ->label('Price (Last Sale Price)')
                    ->getStateUsing(function (PurchaseItem $record) {
                        return $this->getSalesPriceOrDefault($record);
                    }),

                // Total Remaining (Remaining Quantity * Last Sale Price)
                TextColumn::make('total_remaining')
                    ->label('Total Remaining')
                    ->getStateUsing(function (PurchaseItem $record) {
                        $remainingQuantity = $record->quantity;
                        $lastSalePrice = $this->getSalesPriceOrDefault($record);
                        return $remainingQuantity * $lastSalePrice;
                    }),
                ]);
    }

    // Helper method to get sold quantity for a specific purchase
    public function getSoldQuantity($purchaseId)
    {
        return SalesItem::where('purchase_id', $purchaseId)->sum('quantity');
    }

    // Helper method to get the latest sale price or use the purchase sale price if not sold
    public function getSalesPriceOrDefault(PurchaseItem $record)
    {
        // Check if there are any sales records, if so return the latest sale price
        $latestSalePrice = SalesItem::where('purchase_id', $record->id)->latest('created_at')->value('price');

        // If no sale exists, return the purchase sale price
        return $latestSalePrice ?? $record->sale_price;
    }
}
