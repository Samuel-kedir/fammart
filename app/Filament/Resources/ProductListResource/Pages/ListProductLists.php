<?php

namespace App\Filament\Resources\ProductListResource\Pages;

use App\Filament\Resources\ProductListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductLists extends ListRecords
{
    protected static string $resource = ProductListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
