<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesItem extends Model
{
        protected $fillable = ['sale_id', 'product_id', 'price', 'quantity', 'item_total'];

    /**
     * Get the sale that owns this item.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total price for the item based on quantity and price.
     */
    public function calculateItemTotal(): void
    {
        $this->item_total = $this->price * $this->quantity;
        $this->save();
    }
}
