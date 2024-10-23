<?php

namespace App\Filament\Resources\ProductListResource\Pages;

use App\Filament\Resources\ProductListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductList extends EditRecord
{
    protected static string $resource = ProductListResource::class;

    protected function getHeaderActions(): array
    {   
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
