<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'product_name', 'sku', 'quantity', 'unit_price', 'received_quantity'];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_price' => 'float',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute()
    {
        return round($this->quantity * $this->unit_price, 2);
    }

    public function getOutstandingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->received_quantity);
    }
}
