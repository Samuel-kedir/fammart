<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Batch;
use App\Models\Sales;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\Console\Input\Input;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make("sales")
                    ->schema([
                        Select::make('batch_id')
                            // ->relationship('batch', 'batch_id')
                            ->options(Batch::all()->pluck('batch_id', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state, $get) {
                                $batch = Batch::find($state);
                                logger($batch);
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
                            ->debounce(1000)
                            ->minValue(1)
                            ->default(1) // Set default value to 1
                            ->label('Quantity Sold')
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $price = $get('unit_price');
                                // Only update total if both quantity and price are set
                                if ($price !== null && is_numeric($state)) {
                                    $totalPrice = $price * (int)$state;

                                    $set('total', $totalPrice);
                                }
                            }),

                        TextInput::make('unit_price')
                            ->label('Price')
                            ->readOnly() // Automatically filled from product
                            ->required()
                            ->reactive(),

                        TextInput::make('total')
                            ->label('Total')
                            ->readOnly() // Disable input as it's auto-calculated
                    ])
                    ->columns(4) // Full width
                    ->addActionLabel('Add Product Sale')
                    ->minItems(1)
                    ->label('Sales')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Define table columns here if necessary
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
