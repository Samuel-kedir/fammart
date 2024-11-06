<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_price',
        'sale_price',
        'expiry_date',
        'quantity',
        'product_id',
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    
    public function purchase(){
        return $this->belongsTo(Purchase::class,'purchases_id');
    }

}
