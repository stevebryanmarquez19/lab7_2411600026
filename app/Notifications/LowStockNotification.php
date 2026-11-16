<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;
    protected $product;
    public function __construct(Product $product) { $this->product = $product; }
    public function via($notifiable) { return ['database']; }
    public function toArray($notifiable)
    {
        return ['product_id' => $this->product->id, 'sku' => $this->product->sku, 'product_name' => $this->product->name, 'quantity' => $this->product->quantity, 'reorder_level' => $this->product->reorder_level, 'message' => $this->product->name . ' is at or below its reorder level.'];
    }
}
