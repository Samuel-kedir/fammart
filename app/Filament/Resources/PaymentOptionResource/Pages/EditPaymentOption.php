<?php

namespace App\Filament\Resources\PaymentOptionResource\Pages;

use App\Filament\Resources\PaymentOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentOption extends EditRecord
{
    protected static string $resource = PaymentOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
