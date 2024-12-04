<?php

namespace App\Filament\Resources\PaymentOptionResource\Pages;

use App\Filament\Resources\PaymentOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentOption extends CreateRecord
{
    protected static string $resource = PaymentOptionResource::class;
}
