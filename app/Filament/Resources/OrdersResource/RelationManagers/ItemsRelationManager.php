<?php

namespace App\Filament\Resources\OrdersResource\RelationManagers;

use App\Models\Batch;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('batch_id')
                    // ->relationship('items', 'batch_id')
                    ->options(Batch::all()->pluck('batch_id', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state, $get) {
                        $batch = Batch::find($state);
                        logger($batch);
                        if ($batch) {
                            $price = $batch->product->price;
                            $product_name = $batch->product->name;
                            $set('unit_price', $price);
                            $set('name', $product_name);
                            $quantitySold = $get('quantity');
                            $set('total', ($quantitySold ? $quantitySold : 0) * $price);
                        }
                    })
                    ->label('Batch ID')
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                TextInput::make('name')
                    ->label('Name')
                    ->disabled()
                    ->dehydrated(false) // Automatically filled from product
                    ->required()
                    ->reactive(),

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


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('items')
            ->columns([
                Tables\Columns\TextColumn::make('batch.product.name'),
                Tables\Columns\TextColumn::make('batch.batch_id'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit_price'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\TextColumn::make('payment_method'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
