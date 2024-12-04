<?php

namespace App\Filament\Reports;

use App\Models\Sales;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Body\TextColumn;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Header\Layout\HeaderColumn;
use EightyNine\Reports\Components\Header\Layout\HeaderRow;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;

class UsersReport extends Report
{
    public ?string $heading = "Sales Report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                HeaderRow::make()
                    ->schema([
                        HeaderColumn::make()
                            ->schema([
                                Text::make("Sales Report")
                                    ->title()
                                    ->primary(),
                                Text::make("Sales Report By Date"),
                            ]),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Table::make()
                    ->columns([
                        TextColumn::make('id')->label('ID'),
                        TextColumn::make('sum_total')->label('Total Amount'),
                        TextColumn::make('payment_method')->label('Payment Method'),
                        TextColumn::make('created_at')->label('Date'),
                    ])
                    ->data(
                        function (?array $filters)
                        { return $this->fetchFilteredSales($filters);}
                    ),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Text::make('Aggregate')
                    ->primary(),
                ...$this->generateFooterAggregates(),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->placeholder('Select a start date'),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->placeholder('Select an end date'),
            ]);
    }

    private function fetchFilteredSales(?array $filters)
    {
        $query = Sales::query();

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->get();
    }

    private function calculateFilteredAggregates(?array $filters = null): array
    {
        $query = Sales::query();

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query
            ->selectRaw('payment_method, SUM(sum_total) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();
    }

    private function generateFooterAggregates(): array
    {
        $filters = request()->all(); // Fetch current filter values

        $aggregates = $this->calculateFilteredAggregates($filters);

        return array_map(
            fn($method, $total) => Text::make("$method: " . number_format($total, 2)),
            array_keys($aggregates),
            array_values($aggregates)
        );
    }
}
