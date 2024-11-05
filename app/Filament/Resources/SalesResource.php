<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Product;
use App\Models\Sales;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('saleItems')
                    ->live()
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $product = Product::find($state);
                                $price = $product ? $product->price : 0;
                                $set('price', $price);
                                $quantity = $get('quantity') ?? 0;
                                $itemTotal = $price * $quantity;
                                $set('item_total', $itemTotal);
                                // self::setOverallPrice($set, $get);
                            })
                            ->required(),

                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->disabled()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $price = $get('price');
                                $itemTotal = $price * $state;
                                $set('item_total', $itemTotal);
                                // self::setOverallPrice($get, $set);
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

                TextInput::make('overall_price')
                    ->label('Total Price')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->reactive(),
            ]);
    }

    // Helper function to set overall price
    private static function setOverallPrice(Get $get, Set $set): void
    {

        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('saleItems'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
    
        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
    
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
                Tables\Columns\TextColumn::make('created_at')->label('Date'),
                Tables\Columns\TextColumn::make('sum_total')->label('Total Price')->money('USD'),
            ]) ->actions([
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
                        return view('filament.modals.sales-detail-modal', ['saleItems' => $record->saleItems]);
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
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
