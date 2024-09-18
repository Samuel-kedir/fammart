<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'batch_id', 'expiry_date', 'item_count'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     // Automatically generate batch ID when creating a new batch
     public static function boot()
     {
         parent::boot();

         static::creating(function ($batch) {
             if (empty($batch->batch_id)) {
                 $batch->batch_id = Str::random(4); // Generates a 4-character alphanumeric string
             }
         });
     }
}

// Receipt
// id
// Total Price
// payment Method


// Sales
// id
// batch_id
// quantity
// price
// receipt_id (foreign_id)






