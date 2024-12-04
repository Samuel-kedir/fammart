<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentOptionResource\Pages;
use App\Filament\Resources\PaymentOptionResource\RelationManagers;
use App\Models\PaymentOption;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;

class PaymentOptionResource extends Resource
{
    protected static ?string $model = PaymentOption::class;
    protected static ?string $title = 'Payment Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Payment Report';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_option')
                    ->sortable()
                    ->searchable()
                    ->label('Payment Option'),
                TextColumn::make('amount')
                    ->sortable()
                    ->label('Amount')
                    ->money('ETB',true)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
            ])
            ->filters([
                Filter::make('date')
                ->label('Sale Date')
                ->form([
                    Select::make('date_filter')
                        ->label('Select Date Filter')
                        ->options([
                            'all' => 'All Dates', // Option for "All Dates"
                            'single_day' => 'Single Day', // Option for selecting a specific day
                            'date_range' => 'Date Range', // Option for selecting a date range
                        ])
                        ->reactive(),

                    DatePicker::make('date')
                        ->label('Select a Date')
                        ->visible(fn ($get) => $get('date_filter') === 'single_day')
                        ->reactive()
                        ->placeholder('Select a single date'),

                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->visible(fn ($get) => $get('date_filter') === 'date_range')
                        ->reactive()
                        ->placeholder('Start Date'),

                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->visible(fn ($get) => $get('date_filter') === 'date_range')
                        ->reactive()
                        ->placeholder('End Date'),
                ])
                ->query(function ($query, $data) {
                    // Handle the different filters based on the selected options
                    if (isset($data['date_filter'])) {
                        if ($data['date_filter'] === 'single_day' && isset($data['date'])) {
                            // Filter by single date
                            $query->whereDate('created_at', '=', Carbon::parse($data['date'])->format('Y-m-d'));
                        } elseif ($data['date_filter'] === 'date_range' && isset($data['start_date']) && isset($data['end_date'])) {
                            // Filter by date range
                            $query->whereBetween('created_at', [
                                Carbon::parse($data['start_date'])->startOfDay(),
                                Carbon::parse($data['end_date'])->endOfDay()
                            ]);
                        }
                    }
                }),
                // Filter for payment method with "All" option
                Filter::make('payment_option')
                    ->label('Payment Method')
                    ->form([
                        Select::make('payment_option')
                            ->label('Payment Method')
                            ->options(function () {
                                return [
                                    'all' => 'All', // "All" option to show all payment methods
                                    'cash' => 'Cash',
                                    'bank' => 'Bank Transfer',
                                    'pos' => 'POS',
                                    // Add other payment methods as necessary
                                ];
                            })
                            ->reactive()
                    ])
                    ->query(function ($query, $data) {
                        if (isset($data['payment_option']) && $data['payment_option'] !== 'all') {
                            $query->where('payment_option', $data['payment_option']);
                        }
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('payment_option')
                    ->label('Payment Method')
                    ->collapsible(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getActions(): array
    {
        return []; // This will remove the "Create" button
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentOptions::route('/'),
        ];
    }
}
