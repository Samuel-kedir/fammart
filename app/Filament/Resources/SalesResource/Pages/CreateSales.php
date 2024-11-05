<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

     
    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        // Validate incoming data
        $validatedData = Validator::make($data, [
            'saleItems' => 'required|array',
            'saleItems.*.product_id' => 'required|exists:products,id',
            'saleItems.*.price' => 'nullable|numeric',
            'saleItems.*.quantity' => 'required|numeric',
            'saleItems.*.item_total' => 'nullable|numeric',
        ])->validate();

        $sales = Sales::create([
            // Add other fields for the Sales model here if needed
            'sum_total' => array_sum(array_column($validatedData['saleItems'], 'item_total')), // Calculate the overall total
        ]);
        foreach ($validatedData['saleItems'] as $item) {
            $sales->saleItems()->create($item);
        }
        return $sales;
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate overall total before saving
        // dd('Sales form data before creation:', $data['saleItems']);
        foreach($data['saleItems'] as $key=>$sales_item){
            $product=Product::find($sales_item['product_id']);
            $data['saleItems'][$key]['price']=$product->price;
            $data['saleItems'][$key]['item_total']=(float)$product->price * (int)$sales_item['quantity'] ;
        };
        $data['sum_total'] = collect($data['saleItems'] ?? [])->sum(fn($item) => $item['price'] * $item['quantity']);
        return $data;
    }

}
