<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Sent to the person who created the order once it has been approved. */
class PurchaseOrderApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function build()
    {
        $this->purchaseOrder->loadMissing(['supplier', 'items', 'approvedBy']);

        return $this->subject('Purchase order ' . $this->purchaseOrder->reference_number . ' was approved')
            ->view('emails.purchase-order-approved')
            ->with(['order' => $this->purchaseOrder]);
    }
}
