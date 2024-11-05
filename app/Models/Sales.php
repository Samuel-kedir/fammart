<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sales extends Model
{
    use HasFactory;

    protected $fillable = [
        'sum_total',
        'payment_method',
        'items'
    ];

    protected $casts = [
        'items'=>'json'
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'id');
    }
      /**
     * Get the sale items associated with this sale.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Calculate the overall total for the sale.
     */
    public function calculateTotal(): void
    {
        $this->overall_total = $this->saleItems->sum('item_total');
        $this->save();
    }
}
