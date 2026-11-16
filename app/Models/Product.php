<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category', 'unit_price', 'quantity', 'reorder_level', 'status', 'date',
        'supplier_id', 'cost_price', 'pack_size', 'min_order_quantity',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'cost_price' => 'float',
        'pack_size' => 'integer',
        'min_order_quantity' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /** Supported filters: search (name/sku), category, supplier_id, stock (low|out). */
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('sku', 'like', $term);
            });
        }
        if (!empty($filters['category']) && $filters['category'] !== 'All') {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (($filters['stock'] ?? '') === 'low') {
            $query->lowStock();
        } elseif (($filters['stock'] ?? '') === 'out') {
            $query->outOfStock();
        }

        return $query;
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'Out of stock';
        }
        return $this->isLowStock() ? 'Low stock' : 'In stock';
    }

    public function getStockStatusColorAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        return $this->isLowStock() ? 'warning' : 'success';
    }

    public function scopeLowStock(Builder $query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock(Builder $query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory(Builder $query, $category)
    {
        return $query->where('category', $category);
    }
}
