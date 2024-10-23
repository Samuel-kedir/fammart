<?php

namespace App\Filament\Resources\ItemListResource\Pages;

use App\Filament\Resources\ItemListResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateItemList extends CreateRecord
{
    protected static string $resource = ItemListResource::class;


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

    // protected static string $view = 'filament.item-list-page';



    // protected function getViewData(): array
    // {
    //     // Fetch products from the Product model
    //     $products = Product::all();

    //     // Return the products to the view
    //     return array_merge(parent::getViewData(), [
    //         'products' => $products,
    //         'name' => 'sam is here',
    //     ]);
    // }


}
