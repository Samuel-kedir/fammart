<?php
namespace App\Filament\Pages;

use App\Models\Sales;
use Carbon\Carbon;
use Filament\Pages\Page;

class DailySalesReport extends Page
{
    protected static ?string $title = 'Daily Sales Report';

    public $date;
    public $totalSales;
    public $totalItemsSold;
    public $paymentMethodReport;

    public function mount()
    {
        // Set default date to today's date if no date is provided
        $this->date = Carbon::today()->toDateString();

        // Fetch sales data for the given date
        $this->generateReport();
    }

    public function generateReport()
    {
        // Get the sales for the specific date
        $sales = Sales::whereDate('created_at', $this->date)->get();

        // Aggregate the total sales and quantity for the day
        $this->totalSales = $sales->sum('sum_total');
        $this->totalItemsSold = $sales->sum(function ($sale) {
            return $sale->saleItems->sum('quantity');
        });

        // Group sales by payment method
        $this->paymentMethodReport = $sales->groupBy('payment_method')->map(function ($group) {
            return [
                'total_sales' => $group->sum('sum_total'),
                'total_items' => $group->sum(function ($sale) {
                    return $sale->saleItems->sum('quantity');
                }),
            ];
        });
    }

    public function render()
    {
        return view('filament.pages.daily-sales-report');
    }
}
