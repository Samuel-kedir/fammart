<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use App\Models\Item; // Replace with your actual model for items
use App\Models\PurchaseItem;
use Filament\Tables\Columns\TextColumn;

class Report extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report';

    protected function getTableQuery()
    {
        // Adjust the model to fit your actual "items" model
        return PurchaseItem::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Item Name'),
            TextColumn::make('created_at')->label('Created Date')->dateTime('d M Y'),
        ];
    }
}
