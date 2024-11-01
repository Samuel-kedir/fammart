<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;


        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }

        protected function getActions(): array
        {
            return [
                // Add the "Create and Create Another" action
                Actions\Action::make('create_and_create_another')
                    ->label(__('Create and Create Another'))
                    ->icon('heroicon-o-plus')
                    ->action(function () {
                        $this->create();
                    }),

                // Add the "Cancel" action
                Actions\Action::make('cancel')
                    ->label(__('Cancel'))

                    ->action('cancel')
                    ->color('gray'),
            ];
        }

        protected function getFooterActions(): array
        {
            return []; // Return an empty array to remove the default footer actions
        }
}
