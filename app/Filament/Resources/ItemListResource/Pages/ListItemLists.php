<?php

namespace App\Filament\Resources\ItemListResource\Pages;

use App\Filament\Resources\ItemListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemLists extends ListRecords
{
    protected static string $resource = ItemListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
