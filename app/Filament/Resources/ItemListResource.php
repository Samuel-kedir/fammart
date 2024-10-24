<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemListResource\Pages;
use App\Filament\Resources\ItemListResource\RelationManagers;
use App\Models\ItemList;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemListResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $view = '../../resources/views/filament/item-list-page';

    protected static ?string $navigationLabel = 'Item Lists'; // Set this to a suitable label

    protected static string $formName = 'create';

    protected static ?string $title = 'Item List';

    protected static ?string $label = 'Item List';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make(12)
                ->schema([
                    TextInput::make('name')->required()
                    ->autofocus()
                    ->columnSpan(3),
                    TextInput::make('size')
                        ->label('Size')
                        ->placeholder('eg. 250 ml')
                        ->required()
                        ->columnSpan(3),
                    TextInput::make('price')->required()->numeric()
                        ->columnSpan(3)
                        ->prefix('ETB')
                        ->extraAttributes(['style' => 'text-align: right; width: 100%;']),
                    Textarea::make('description')
                    ->columnSpan(3)
                    ->rows(1)
                ])

                ->extraAttributes(['style' => 'display: flex; gap: 10px; align-items: center; justify-content: space-between']),


                Forms\Components\View::make('components.item-list')
                    ->label('Item List')
                    ->viewData([
                        'products' => Product::orderBy('updated_at','desc')->get(), // Fetch all products
                    ])->extraAttributes(['class'=>'w-[100vw]'])


            ]);



    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable()
                ->sortable(),
                TextColumn::make('size')
                ->sortable()
                ->searchable(),
                TextColumn::make('price')
                ->sortable()
                ->searchable()
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
            //
        ];
    }

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemLists::route('/'),
            'create' => Pages\CreateItemList::route('/create'),
            'edit' => Pages\EditItemList::route('/{record}/edit'),
        ];
    }
}
