<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    //
    protected $fillable = [
        'sales_id',
        'payment_option',
        'amount'
    ];
}
