<?php
namespace App\Filament\Pages;

use App\Models\Sales;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;

class DailySalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Daily Sales Report';
    protected static string $view = 'filament.pages.daily-sales-report';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public $selectedDate;
    public $data;
    
    // restrict access for sale
    use HasPageShield;

    public function table(Table $table): Table
    {
        return $table
            ->query(Sales::whereDate('created_at', $this->selectedDate)) // Filter sales for the selected date
            ->columns([
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),

                TextColumn::make('sum_total')
                    ->label('Sales Amount')
                    ->money('usd')
                    ->sortable(),

                // You can add more columns as necessary
            ]);
    }

    // Helper method to generate report for the selected date
    public function generateReport($date)
    {
        $sales = Sales::whereDate('created_at', $date)
            ->get()
            ->groupBy('payment_method'); // Group sales by payment method

        $totalSalesByMethod = [];
        $totalSales = 0;

        // Group sales by payment method and calculate total sales for each method
        foreach ($sales as $paymentMethod => $salesGroup) {
            $totalSalesByMethod[$paymentMethod] = $salesGroup->sum('sum_total');
            $totalSales += $totalSalesByMethod[$paymentMethod];
        }

        return [
            'salesByMethod' => $totalSalesByMethod,
            'totalSales' => $totalSales,
        ];
    }

    public function mount()
    {
        // Set default selected date to today's date
        $this->selectedDate = Carbon::today()->toDateString();
        $this->data = $this->generateReport($this->selectedDate);
    }

    public function updatedSelectedDate()
    {
        // Re-generate report when the selected date changes
        $this->data = $this->generateReport($this->selectedDate);
    }
}
