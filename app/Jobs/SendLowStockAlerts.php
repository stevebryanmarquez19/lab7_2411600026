<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLowStockAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $productId;
    public function __construct($productId) { $this->productId = $productId; }
    public function handle()
    {
        $product = \App\Models\Product::find($this->productId);
        if (!$product || $product->quantity > $product->reorder_level) return;
        User::query()->each(function ($user) use ($product) {
            $alreadyUnread = $user->unreadNotifications()->get()->contains(function ($notification) use ($product) {
                return (int) data_get($notification->data, 'product_id') === (int) $product->id;
            });
            if (!$alreadyUnread) {
                $user->notify(new LowStockNotification($product));
            }
        });
    }
}
