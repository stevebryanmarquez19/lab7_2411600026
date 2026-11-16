<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use App\Support\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to approvers when a purchase order is submitted for approval.
 * ShouldQueue: delivery happens on the queue worker (QUEUE_CONNECTION=database) or immediately
 * when QUEUE_CONNECTION=sync, which is handy while testing with Mailtrap.
 */
class PurchaseOrderCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function build()
    {
        $this->purchaseOrder->loadMissing(['supplier', 'items', 'createdBy']);

        $mail = $this->subject('Purchase order ' . $this->purchaseOrder->reference_number . ' is awaiting approval')
            ->view('emails.purchase-order-created')
            ->with(['order' => $this->purchaseOrder]);

        // Attach a PDF copy of the order when dompdf is installed.
        if (Pdf::available()) {
            $pdf = Pdf::make('purchase-orders.pdf', ['order' => $this->purchaseOrder]);
            $mail->attachData($pdf->output(), $this->purchaseOrder->reference_number . '.pdf', ['mime' => 'application/pdf']);
        }

        return $mail;
    }
}
