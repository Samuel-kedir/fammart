<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Date;

class PurchaseResource extends Resource
{
    protected static ?string $model = PurchaseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    private $products;

    // public function mount()
    // {
    //     $this->products = Product::all()->pluck('name', 'id'); // Fetch products initially
    // }

    // public function createProduct(array $data)
    // {
    //     // Create a new product
    //     Product::create($data);

    //     // Refresh products
    //     $this->products = Product::all(); // Refresh the product list
    // }


    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Grid::make(12)
                ->schema([
                    Select::make('product_id')
                    ->options(Product::all()->pluck('name', 'id')->map(fn($name, $id) => $name . ' ( ' . Product::find($id)->size . ')'))
                            // ->relationship('product','name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Product Name')
                            ->createOptionForm([
                                Grid::make(3)->schema([
                                    TextInput::make('name')->required()->label('Product Name'),
                                    TextInput::make('size')->label('Size'),
                                    Textarea::make('description')->label('Description')->rows(1 ),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Create the new product and return the ID of the newly created product
                                $product = Product::create($data);
                                return $product->id;  // Return product ID, not name
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                // Ensure the product_id is correctly set in the form state
                                $set('product_id', $state);  // This ensures the correct state is passed to the product_id field
                            })
                            ->columnSpan(3), // Create product

                    DatePicker::make('expiry_date')
                        ->label('EXP Date')
                        ->placeholder('eg. 250 ml')
                        ->required()
                        ->columnSpan(3),
                    TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->required()
                        ->columnSpan(3),
                    TextInput::make('purchase_price')->required()->numeric()
                        ->columnSpan(3)
                        ->prefix('ETB')
                        ->extraAttributes(['style' => 'text-align: right; width: 100%;']),
                        // ->afterStateUpdated(function (callable $set, $state, $get) {
                        //     $set('sale_price',$state);
                        //     }),
                    TextInput::make('sale_price')->numeric()
                        ->columnSpan(3)
                        ->live()
                        ->required()
                        ->prefix('ETB')
                        ->extraAttributes(['style' => 'text-align: right; width: 100%;']),
                ]),

                // ->extraAttributes(['style' => 'display: flex; gap: 10px; align-items: center; justify-content: space-between']),


                Forms\Components\View::make('components.purchase-list')
                    ->label('Purchased Items')
                    ->viewData([
                        'products' => PurchaseItem::with('product')->orderBy('updated_at','desc')->get(), // Fetch all products
                    ])->extraAttributes(['class'=>'w-[100vw]'])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Item id'),
                Tables\Columns\TextColumn::make('product.name')
                                            ->label('Product Name')
                                            ->sortable()  // Optional: Allow sorting by product name
                                            ->searchable(), // Optional: Make the column searchable
                Tables\Columns\TextColumn::make('purchase_price')->label('Purchase Price'),
                Tables\Columns\TextColumn::make('expiry_date')->sortable()->label('EXP-Date'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
