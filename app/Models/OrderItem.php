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
            $orderItem->deductProduct($orderItem);
        });

        static::deleted(function (OrderItem $orderItem) {
            $orderItem->updateOrderTotal();
            $orderItem->deductProduct($orderItem);
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

    public function deductProduct($orderItem)
    {
        $batch = $this->batch;

        if ($batch && $batch->item_count > 0) {

            $item_count = $batch->item_count - $orderItem->quantity;
            $batch->update(["item_count" => $item_count]);
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
