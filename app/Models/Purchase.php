<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    // use HashFactory;

    // protected $fillable = ['vendor_name'];
    //

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class,'purchases_id');
    }
}
