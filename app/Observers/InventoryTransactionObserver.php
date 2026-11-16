<?php

namespace App\Observers;

use App\Models\InventoryTransaction;
use App\Events\StockLevelChanged;
use Illuminate\Support\Facades\DB;

class InventoryTransactionObserver
{
    public function creating(InventoryTransaction $transaction)
    {
        $product = $transaction->product()->lockForUpdate()->firstOrFail();
        if ($transaction->type === 'opening') {
            $transaction->quantity_before = 0;
            $transaction->quantity_after = $transaction->quantity;
            return;
        }
        $transaction->quantity_before = $product->quantity;
        if ($transaction->type === 'out' && $product->quantity < $transaction->quantity) {
            throw new \RuntimeException('Not enough stock available for this stock-out transaction.');
        }
        $transaction->quantity_after = $transaction->type === 'in'
            ? $product->quantity + $transaction->quantity
            : ($transaction->type === 'out'
                ? $product->quantity - $transaction->quantity
                : ($transaction->type === 'adjustment' ? $transaction->quantity : $product->quantity));
    }

    public function created(InventoryTransaction $transaction)
    {
        if (DB::getDriverName() !== 'mysql' && in_array($transaction->type, ['in', 'out', 'adjustment'])) {
            $product = $transaction->product()->lockForUpdate()->firstOrFail();
            $product->quantity = $transaction->quantity_after;
            $product->save();
        }
        $product = $transaction->product()->firstOrFail();
        event(new StockLevelChanged($product, $transaction));
    }
}
