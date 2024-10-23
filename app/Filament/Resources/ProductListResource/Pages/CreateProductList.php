<?php

namespace App\Filament\Resources\ProductListResource\Pages;

use App\Filament\Resources\ProductListResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductList extends CreateRecord
{
    protected static string $resource = ProductListResource::class;
}
