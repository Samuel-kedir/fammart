<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Batch;
use App\Models\Sales;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ButtonAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\Input;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('sum_total')->label('Sum Total')->readOnly()->default(0),

            Grid::make(1)->schema([
                Repeater::make('items')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('batch_id')
                                ->options(Batch::all()->pluck('batch_id', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, $state, $get) {
                                    $batch = Batch::find($state);
                                    if ($batch) {
                                        $price = $batch->product->price;
                                        $set('unit_price', $price);
                                        $quantitySold = $get('quantity');
                                        $set('total', ($quantitySold ? $quantitySold : 0) * $price);
                                    }
                                })
                                ->label('Batch ID')
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->debounce(500)
                                ->minValue(1)
                                ->default(1)
                                ->label('Quantity Sold')
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    $price = $get('unit_price');
                                    if ($price !== null && is_numeric($state)) {
                                        $totalPrice = $price * (int) $state;
                                        $set('total', $totalPrice);
                                    }

                                    // Update sum_total
                                    $items = $get('items') ?? [];
                                    $sumTotal = 0;
                                    foreach ($items as $item) {
                                        $sumTotal += $item['total'] ?? 0;
                                    }
                                    $set('sum_total', $sumTotal);
                                }),

                            TextInput::make('unit_price')->label('Price')->readOnly()->required()->reactive(),

                            TextInput::make('total')->label('Total')->readOnly(),
                        ]),
                    ])
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $items = $get('items') ?? [];
                        $sumTotal = 0;
                        if (is_array($items)) {
                            foreach ($items as $item) {
                                $sumTotal += $item['total'] ?? 0;
                            }
                        }
                        $set('sum_total', $sumTotal);
                    }),

                Grid::make(2)->schema([
                    // Adding sum total field at the end of the repeater

                    // Adding payment fields
                    Select::make('payment_method')
                        ->label('Payment Method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Deposit',
                            'pos' => 'POS',
                        ])
                        ->required(),
                ]),

                // TextInput::make('tax')
                //     ->label('Tax (%)')
                //     ->numeric()
                //     ->default(0),

                // TextInput::make('discount')
                //     ->label('Discount (%)')
                //     ->numeric()
                //     ->default(0),

                // TextInput::make('additional_fees')
                //     ->label('Additional Fees (%)')
                //     ->numeric()
                //     ->default(0),

                // Adding a finalize sale button
            ]),
        ]);
    }

    public static function finalizeSale(array $data)
    {
        $sumTotal = 0;

        // Validate and process the sale
        foreach ($data['items'] as $item) {
            $batch = Batch::find($item['batch_id']);
            if ($batch->item_count < $item['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity exceeds available stock for batch ' . $batch->batch_id,
                ]);
            }

            // Deduct the stock from the batch
            $batch->decrement('item_count', $item['quantity']);

            // Accumulate the total for the sale
            $sumTotal += $item['total'];
        }

        // Create a single sale record with sum_total and payment method
        Sales::create([
            'sum_total' => $sumTotal, // Save the accumulated sum_total for the transaction
            'payment_method' => $data['payment_method'], // Payment method for the whole sale
            'items' => ($data['items']), // Save all items as JSON
        ]);

        // Notification after successful sale finalization
        Notification::make()
        ->title('Sale finalized successfully!')
        ->success()
        ->send();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('created_at')->label('Date and Time')->searchable()->sortable()->formatStateUsing(fn($state) => Carbon::parse($state)->format('d M Y, g:i A')), TextColumn::make('sum_total')])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }

    public static function handleRecordCreation(array $data): array
    {
        // Ensure that 'sales' key contains data from the repeater
        if (isset($data['sales']) && is_array($data['sales'])) {
            foreach ($data['sales'] as $sale) {
                Sales::create([
                    'batch_id' => $sale['batch_id'],
                    'quantity' => $sale['quantity'],
                    'unit_price' => $sale['unit_price'],
                    'total' => $sale['total'],
                    'payment_method' => $data['payment_method'], // Assuming you have a single payment method for all entries
                ]);
            }
        }

        // Remove 'sales' key if you don't want to save it in the main table
        unset($data['sales']);

        return $data;
    }
}
