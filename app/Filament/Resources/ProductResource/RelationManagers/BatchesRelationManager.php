<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('batch_id')
                //     ->label('Batch ID')
                //     ->disabled() ,

                // Forms\Components\Select::make('product_id')
                //     ->relationship('product', 'name')
                //     ->required(),.


                Forms\Components\DatePicker::make('expiry_date')
                    ->required(),

                Forms\Components\TextInput::make('item_count')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_id')
            ->columns([
                TextColumn::make('batch_id'),
                TextColumn::make('expiry_date'),
                TextColumn::make('item_count'),
                TextColumn::make('item_count')
                    ->label('Item Count')
                    ->color(fn(int $state): string => $state > 0 ? 'white' : 'danger') // Set color based on item count
                    ->formatStateUsing(fn(int $state): string => number_format($state)),


                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->color(fn(string $state): string => Carbon::parse($state)->isPast() ? 'danger' : 'white') // Set color based on expiry date
                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->toDateString()),
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
