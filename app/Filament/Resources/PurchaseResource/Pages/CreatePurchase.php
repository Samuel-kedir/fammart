<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\PurchaseItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    // Override the redirection URL after creation
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }

    // Define additional actions (e.g., Create and Create Another, Cancel)
    protected function getActions(): array
    {
        return [
            Actions\Action::make('create_and_create_another')
                ->label(__('Create and Create Another'))
                ->icon('heroicon-o-plus')
                ->action(function () {
                    $this->create();
                }),

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

    // Override to handle record creation with logic to check existing purchases
    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        try {
            // Check if a purchase with the same product_id and expiry_date exists
            $existingPurchase = PurchaseItem::where('product_id', $data['product_id'])
                ->where('expiry_date', $data['expiry_date'])
                ->first();

            if ($existingPurchase) {
                // If it exists, increment the quantity
                if($data['sale_price']){
                    $existingPurchase->sale_price = $data['sale_price'];
                }
                $existingPurchase->quantity += $data['quantity'];
                $existingPurchase->save();
                $purchase = $existingPurchase;  // Return the updated purchase



            } else {
                // If no existing record, create a new purchase item
                if($data['sale_price']==Null){
                    $data['sale_price'] = $data['purchase_price'];
                }
                $purchase = PurchaseItem::create($data);

            }

            // dd($data);

            DB::commit();

            return $purchase;
        } catch (\Exception $exception) {
            DB::rollBack();
            dump($exception);
            return null;
        }
    }

    // Mutate form data before creation (preprocess the data)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // You can perform additional calculations here if needed, for example, to adjust fields.
        // For now, we just return the data as is.
        return $data;
    }
}
