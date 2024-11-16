<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Sales;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([

                TableRepeater::make('saleItems')
                    ->live()

                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(function () {
                                // Retrieve PurchaseItems with products, including expiration date in the label
                                return PurchaseItem::with('product')
                                    ->get()
                                    ->mapWithKeys(function ($purchaseItem) {
                                        $productName = $purchaseItem->product->name ?? 'Unknown Product';
                                        $productSize = $purchaseItem->product->size ?? 'Unknown Size';
                                        $expiryDate = $purchaseItem->expiry_date
                                        ? Carbon::parse($purchaseItem->expiry_date)->format('d M Y')
                                        : 'No Expiry Date';  // Adjust the field name as necessary
                                        $label = "{$productName} - {$productSize} -  {$expiryDate}";
                                        return [$purchaseItem->id => $label];
                                    });
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Fetch the selected PurchaseItem with its related product
                                $purchaseItem = PurchaseItem::with('product')->find($state);

                                // Retrieve the product price if available, else default to 0
                                $price = $purchaseItem ? $purchaseItem->sale_price : 0;
                                $quantity = $purchaseItem ? $purchaseItem->quantity : 0;
                                $set('price', $price);
                                // $set('quantity_placeholder', $quantity);
                                $set('max_quantity', $quantity);

                                // Calculate item total based on the current quantity
                                $quantity = $get('quantity') ?? 0;
                                $itemTotal = $price * $quantity;
                                $set('item_total', $itemTotal);
                            })
                            ->required(),



                            TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->disabled()
                            ->required(),

                            TextInput::make('quantity')
                            ->label('Quantity')
                            // ->placeholder(function ($get) {
                            //     // Use the row-specific placeholder value
                            //     return $get('quantity_placeholder') ?? 'Enter quantity';
                            // })
                            ->debounce(1000)
                            ->numeric()
                            ->required()
                            // ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $price = $get('price');
                                $itemTotal = $price * $state;
                                $set('item_total', $itemTotal);
                            })
                            ->maxValue(function ($get) {
                                // Dynamically set the max value based on the available quantity
                                return $get('max_quantity') ?? 250;
                            }),


                        TextInput::make('item_total')
                            ->label('Item Total')
                            ->numeric()
                            ->disabled()
                            ->required(),
                    ])
                    ->columns(4)
                    ->afterStateUpdated(function (Get $get, Set $set){
                        self::setOverallPrice($get, $set);
                    }),


                ]),
                TextInput::make('overall_price')
                    ->label('Total Price')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->reactive(),
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
            ]);
    }

    // Helper function to set overall price
    private static function setOverallPrice(Get $get, Set $set): void
    {

        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('saleItems'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // Retrieve prices for all selected products
        $prices = PurchaseItem::find($selectedProducts->pluck('product_id'))->pluck('sale_price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
        $set('overall_price', number_format($subtotal, 2, '.', ''));
        // $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100)), 2, '.', ''));

    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Sale ID'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d M Y')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('payment_method')->label('payment method')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('sum_total')->label('Total Price')->money('ETB')->sortable()->searchable(),
            ])
            ->actions([
                // Remove the edit action and add a custom view action
                     Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Sale Details')
                    ->modalWidth('2xl')
                    ->action(function ($record, $set) {
                        // You can load the sale and its related sale items here
                        $saleItems = $record->saleItems()->get();

                        // Set the data to display in the modal
                        $set('saleItems', $saleItems);
                    })
                    ->modalContent(function ($record) {
                        return view('filament.modals.sales-detail-modal', ['saleItems' => $record->saleItems, 'record'=> $record]);
                    }),
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
                Filter::make('payment_method')
                    ->label('Payment Method')
                    ->form([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(function () {
                                return [
                                    'all' => 'All', // "All" option to show all payment methods
                                    'cash' => 'Cash',
                                    'credit_card' => 'Credit Card',
                                    'pos' => 'POS',
                                    // Add other payment methods as necessary
                                ];
                            })
                            ->reactive()
                    ])
                    ->query(function ($query, $data) {
                        if (isset($data['payment_method']) && $data['payment_method'] !== 'all') {
                            $query->where('payment_method', $data['payment_method']);
                        }
                    }),
            ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            // 'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
