<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Call finalizeSale in the SalesResource
        SalesResource::finalizeSale($data);

        return $data;
    }
}
