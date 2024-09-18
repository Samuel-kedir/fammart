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

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('sales')
                    ->schema([
                        Select::make('batch_id')
                            ->relationship('batch', 'batch_id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state, $get) {
                                $batch = Batch::find($state);
                                if ($batch) {
                                    $price = $batch->product->price;
                                    $set('price', $price);
                                    $quantitySold = $get('quantity_sold');
                                    $set('total', ($quantitySold ? $quantitySold : 0) * $price);
                                }
                            })
                            ->label('Batch ID'),

                        TextInput::make('quantity_sold')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->debounce(1000)
                            ->minValue(1)
                            ->default(1) // Set default value to 1
                            ->label('Quantity Sold')
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $price = $get('price');
                                // Only update total if both quantity and price are set
                                if ($price !== null && is_numeric($state)) {
                                    $totalPrice = $price * (int)$state;

                                    $set('total', $totalPrice);
                                }
                            }),

                        TextInput::make('price')
                            ->label('Price')
                            ->disabled() // Automatically filled from product
                            ->required()
                            ->reactive(),

                        TextInput::make('total')
                            ->label('Total')
                            ->disabled() // Disable input as it's auto-calculated
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
}
