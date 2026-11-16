<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLevelChanged
{
    use Dispatchable, SerializesModels;
    public $product;
    public $transaction;
    public function __construct(Product $product, $transaction)
    {
        $this->product = $product;
        $this->transaction = $transaction;
    }
}
