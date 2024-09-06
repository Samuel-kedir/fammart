<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\BatchesRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
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
                    Grid::make(12) // Creating a 3-column grid for even layout distribution
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
                    ->extraAttributes(['style' => 'display: flex; gap: 10px; align-items: center; justify-content: space-between']), // Flexbox for better alignment
                Textarea::make('description'),

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
