<?php

namespace App\Listeners;

use App\Events\StockLevelChanged;
use App\Jobs\SendLowStockAlerts;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckLowStock implements ShouldQueue
{
    public function handle(StockLevelChanged $event)
    {
        if ($event->product->quantity <= $event->product->reorder_level) {
            SendLowStockAlerts::dispatch($event->product->id);
        }
    }
}
