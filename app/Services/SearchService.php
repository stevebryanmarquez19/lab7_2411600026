<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;

/**
 * One place for the complex list searches. Each method returns an Eloquent builder so the
 * caller decides whether to paginate (screens) or ->get() (exports). Filtering itself lives in
 * each model's scopeFilter(), so the same rules are reused everywhere.
 */
class SearchService
{
    /** name, SKU, category, supplier */
    public function searchProducts(array $filters)
    {
        return Product::with('supplier')->filter($filters)->orderBy('name');
    }

    /** reference, supplier, status, date range (eager loads supplier + creator to avoid N+1 queries) */
    public function searchPurchaseOrders(array $filters)
    {
        return PurchaseOrder::with(['supplier', 'createdBy'])->filter($filters)->latest('order_date')->latest('id');
    }

    /** product, type, user, date range */
    public function searchTransactions(array $filters)
    {
        return InventoryTransaction::with(['product', 'user'])->filter($filters)->latest()->latest('id');
    }

    public function searchSuppliers(array $filters)
    {
        return Supplier::filter($filters);
    }
}
