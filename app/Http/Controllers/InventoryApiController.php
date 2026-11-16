<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryTransaction;

class InventoryApiController extends Controller
{
    public function summary()
    {
        $products = Product::query();
        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'total_products' => (clone $products)->count(),
            'total_quantity' => (clone $products)->sum('quantity'),
            'low_stock_count' => (clone $products)->whereColumn('quantity', '<=', 'reorder_level')->count(),
            'low_stock' => (clone $products)->whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity')->limit(10)->get(['id', 'name', 'sku', 'quantity', 'reorder_level']),
            'recent_transactions' => $this->recentTransactionData(),
        ]);
    }

    public function lowStock()
    {
        return response()->json([
            'data' => Product::whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity')->get(['id', 'name', 'sku', 'quantity', 'reorder_level']),
        ]);
    }

    public function recentTransactions()
    {
        return response()->json(['data' => $this->recentTransactionData()]);
    }

    private function recentTransactionData()
    {
        return InventoryTransaction::with('product:id,name,sku')->latest()->limit(8)->get()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'product' => optional($tx->product)->name,
                'sku' => optional($tx->product)->sku,
                'type' => $tx->type,
                'quantity' => $tx->quantity,
                'quantity_before' => $tx->quantity_before,
                'quantity_after' => $tx->quantity_after,
                'actor' => $tx->actor_name,
                'created_at' => optional($tx->created_at)->toDateTimeString(),
            ];
        });
    }
}
