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
                    ->label('Product Name'),

                TextColumn::make('quantity')
                    ->label('Purchased Quantity'),

                TextColumn::make('purchase_price')
                    ->label('Purchase Price'),

                // Show the count of SalesItem entries for this product
                TextColumn::make('sales_item_count')
                    ->label('Sales Item Count')
                    ->getStateUsing(function ($record) {
                        return $this->salesItemCount($record->product_id);
                    }),

                // Remaining Quantity (Purchased Quantity - Sold Quantity)
                TextColumn::make('remaining_quantity')
                    ->label('Remaining Quantity')
                    ->getStateUsing(function ($record) {
                        $purchasedQuantity = $record->quantity;
                        $soldQuantity = SalesItem::where('product_id', $record->product_id)->sum('quantity');
                        return $purchasedQuantity - $soldQuantity;
                    }),

                // Total Sales Income
                TextColumn::make('total_sales_income')
                    ->label('Total Sales Income')
                    ->getStateUsing(function ($record) {
                        return $this->calculateTotalSalesIncome($record->product_id);
                    }),

                // Total Profit
                TextColumn::make('total_profit')
                    ->label('Total Profit')
                    ->getStateUsing(function ($record) {
                        return $this->calculateTotalProfit($record->product_id, $record->purchase_price);
                    }),
            ]);
    }

    // Helper method to count sales for a specific product
    public function salesItemCount($productId)
    {
        return SalesItem::where('product_id', $productId)->count();
    }

    // Helper method to calculate total sales income for a product
    public function calculateTotalSalesIncome($productId)
    {
        $totalIncome = SalesItem::where('product_id', $productId)
            ->get()
            ->sum(function ($salesItem) {
                return $salesItem->quantity * $salesItem->price;
            });

        return $totalIncome;
    }

    // Helper method to calculate total profit for a product
    public function calculateTotalProfit($productId, $purchasePrice)
    {
        // Get sold quantity for the product
        $soldQuantity = SalesItem::where('product_id', $productId)->sum('quantity');

        // Calculate total sales income for the product
        $totalSalesIncome = $this->calculateTotalSalesIncome($productId);

        // Calculate the profit (Sales Income - (Purchase Price * Sold Quantity))
        return $totalSalesIncome - ($purchasePrice * $soldQuantity);
    }
}
