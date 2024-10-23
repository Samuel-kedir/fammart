<?php

namespace App\Filament\Resources\ItemListResource\Pages;

use App\Filament\Resources\ItemListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemList extends EditRecord
{
    protected static string $resource = ItemListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
            ->label('save changes')
            ->action('save')
            ->color('success'),
            Actions\DeleteAction::make()

        ];
    }



}
