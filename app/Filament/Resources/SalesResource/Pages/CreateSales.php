<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Sales;
use App\Models\SalesItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;



     // Override the redirection URL after creation
    protected function getRedirectUrl(): string
    {
        // Redirect to the Sales index page
        return SalesResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        // Validate incoming data
        $validatedData = Validator::make($data, [
            'saleItems' => 'required|array',
            'saleItems.*.purchase_id' => 'required|exists:purchase_items,id',
            'saleItems.*.price' => 'nullable|numeric',
            'saleItems.*.quantity' => 'required|numeric',
            'saleItems.*.item_total' => 'nullable|numeric',
        ])->validate();

        DB::beginTransaction();
        try{
            $sales = Sales::create([
                // Add other fields for the Sales model here if needed
                'sum_total' => array_sum(array_column($validatedData['saleItems'], 'item_total')), // Calculate the overall total
                'payment_method' => $data['payment_method'],
                'phone'=>$data['phone'],
                'discount'=>(float)$data['discount'],
            ]);
            foreach ($validatedData['saleItems'] as $item) {
                $sales->saleItems()->create($item);

                $purchase=PurchaseItem::where('id',$item['purchase_id'])->first();

                $purchase->quantity=(float)$purchase->quantity - (float)$item["quantity"];
                $purchase->save();
            }

            DB::commit();

            return $sales;
        }catch(\Exception $exception){
            DB::rollBack();
            dump($exception);
            return NULL;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate overall total before saving
        // dd('Sales form data before creation:', $data['saleItems']);
        foreach($data['saleItems'] as $key=>$sales_item){
            $product=PurchaseItem::find($sales_item['purchase_id']);
            $data['saleItems'][$key]['price']=$product->sale_price;
            $data['saleItems'][$key]['item_total']=(float)$product->sale_price * (int)$sales_item['quantity'] ;
        };
        $data['sum_total'] = collect($data['saleItems'] ?? [])->sum(fn($item) => $item['price'] * $item['quantity']);
        return $data;
    }

}
