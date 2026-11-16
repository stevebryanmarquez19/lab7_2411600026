<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = ['product_id', 'user_id', 'actor_name', 'type', 'quantity', 'quantity_before', 'quantity_after', 'reference', 'notes'];
    protected $casts = ['quantity' => 'integer', 'quantity_before' => 'integer', 'quantity_after' => 'integer'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }

    /** Supported filters: product_id, search (product name/SKU), type, user (name), date_from, date_to. */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->whereHas('product', function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('sku', 'like', $term);
            });
        }
        if (!empty($filters['type']) && in_array($filters['type'], ['opening', 'in', 'out', 'adjustment'], true)) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['user'])) {
            $term = '%' . $filters['user'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('actor_name', 'like', $term)
                  ->orWhereHas('user', function ($u) use ($term) { $u->where('name', 'like', $term); });
            });
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
