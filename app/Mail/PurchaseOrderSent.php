<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use App\Support\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** The formal order e-mailed to the supplier. */
class PurchaseOrderSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function build()
    {
        $this->purchaseOrder->loadMissing(['supplier', 'items']);

        $mail = $this->subject('Purchase order ' . $this->purchaseOrder->reference_number . ' from ' . config('app.name'))
            ->view('emails.purchase-order-sent')
            ->with(['order' => $this->purchaseOrder]);

        if (Pdf::available()) {
            $pdf = Pdf::make('purchase-orders.pdf', ['order' => $this->purchaseOrder]);
            $mail->attachData($pdf->output(), $this->purchaseOrder->reference_number . '.pdf', ['mime' => 'application/pdf']);
        }

        return $mail;
    }
}
