<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;


    protected static function booted()
    {
        static::saved(function (OrderItem $orderItem) {
            $orderItem->updateOrderTotal();
        });

        static::deleted(function (OrderItem $orderItem) {
            $orderItem->updateOrderTotal();
        });
    }

    public function updateOrderTotal()
    {
        $order = $this->order;

        if ($order) {
            // Sum the total prices of all related order items
            $totalPrice = $order->items()->sum('total');
            $order->update(['total_price' => $totalPrice]);
        }
    }

    protected $fillable = [
        'batch_id',
        'quantity',
        'unit_price',
        'total',
        'payment_method',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
