<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save_changes')
            ->label('Save Changes')
            ->action(fn () => $this->save()) // Calls the save method to update the record
            ->color('primary'),
            Actions\DeleteAction::make(),
        ];
    }
}
