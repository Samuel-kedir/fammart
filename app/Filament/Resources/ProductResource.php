<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\BatchesRelationManager;
use App\Models\Batch;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nette\Utils\Html;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        TextInput::make('name')->required(),
        Select::make('category_id')
            ->relationship('category', 'name')
            ->createOptionForm([
                TextInput::make('name')->required(),
            ])
            ->required(),
        Grid::make(12)
            ->schema([
                TextInput::make('size_value')
                    ->label('Size Value')
                    ->numeric()
                    ->required()
                    ->columnSpan(3),
                Select::make('size_unit')
                    ->label('Size Unit')
                    ->options([
                        'kg' => 'Kilogram',
                        'g' => 'Gram',
                        'lb' => 'Pound',
                        'oz' => 'Ounce',
                    ])
                    ->required()
                    ->columnSpan(3)
                    ->extraAttributes(['style' => 'text-align: right; width: 100%; margin-right:100px ']),
                TextInput::make('price')->required()->numeric()
                    ->columnSpan(6)
                    ->prefix('ETB')
                    ->extraAttributes(['style' => 'text-align: right; width: 100%;']),
            ])
            ->extraAttributes(['style' => 'display: flex; gap: 10px; align-items: center; justify-content: space-between']),
        Textarea::make('description'),
        TextInput::make('sum_total')
            ->label('Sum Total')
            ->readOnly(),
        Repeater::make('items')
            ->schema([
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
                    ->debounce(500) // Adding debounce for smoother real-time updates
                    ->minValue(1)
                    ->default(1)
                    ->label('Quantity Sold')
                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                        $price = $get('unit_price');
                        if ($price !== null && is_numeric($state)) {
                            $totalPrice = $price * (int)$state;
                            $set('total', $totalPrice);
                        }

                        // Move this calculation outside of the quantity update:
                        // The total will update as soon as other fields are updated
                        $items = $get('items') ?? []; // Ensure $items is an array
                        $sumTotal = 0;
                        foreach ($items as $item) {
                            $sumTotal += $item['total'] ?? 0;
                        }
                        $set('sum_total', $sumTotal);
                    }),

                TextInput::make('unit_price')
                    ->label('Price')
                    ->readOnly()
                    ->required()
                    ->reactive(),
                TextInput::make('total')
                    ->label('Total')
                    ->readOnly(),
            ])
            ->afterStateUpdated(function (callable $set, callable $get) {
                $items = $get('items') ?? []; // Ensure $items is at least an empty array
                $sumTotal = 0;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $sumTotal += $item['total'] ?? 0;
                    }
                }
                $set('sum_total', $sumTotal);
            }),

    ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('size_value')
                ->label('Size')
                ->formatStateUsing(fn ($state, $record) => $record->size_value . ' ' . $record->size_unit)
                ->sortable()
                ->searchable(),
                TextColumn::make('price'),
                TextColumn::make('category.name'),
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

    public static function getRelations(): array
    {
        return [
            BatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
