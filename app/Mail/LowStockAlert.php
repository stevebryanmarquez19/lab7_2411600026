<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Daily summary for administrators: what needed reordering and which draft orders were created. */
class LowStockAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $products;
    public $purchaseOrders;

    /**
     * @param \Illuminate\Support\Collection $products        products that triggered a reorder
     * @param \Illuminate\Support\Collection $purchaseOrders  draft orders that were generated
     */
    public function __construct($products, $purchaseOrders)
    {
        $this->products = $products;
        $this->purchaseOrders = $purchaseOrders;
    }

    public function build()
    {
        return $this->subject('Reorder check: ' . $this->purchaseOrders->count() . ' draft purchase order(s) need review')
            ->view('emails.low-stock-alert')
            ->with(['products' => $this->products, 'orders' => $this->purchaseOrders]);
    }
}
