<?php

namespace App\Observers;

use App\Mail\PurchaseOrderApproved;
use App\Mail\PurchaseOrderCreated;
use App\Mail\PurchaseOrderSent;
use App\Models\ActivityLog;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\DashboardWidgetService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Reacts to purchase order changes: writes the approval history and sends status e-mails.
 * Keeping this here (instead of in the controller) means orders changed by the scheduler,
 * seeders or tinker are audited the same way.
 */
class PurchaseOrderObserver
{
    public function created(PurchaseOrder $order)
    {
        ActivityLog::record($order, 'created', null, $order->status, $order->auto_generated ? 'Generated automatically by the reorder check.' : $order->statusComment);
        DashboardWidgetService::flush();
    }

    public function updated(PurchaseOrder $order)
    {
        DashboardWidgetService::flush();

        if (!$order->wasChanged('status')) {
            return;
        }

        $from = $order->getOriginal('status');
        ActivityLog::record($order, $order->status, $from, $order->status, $order->status === PurchaseOrder::REJECTED ? $order->rejection_reason : $order->statusComment);

        switch ($order->status) {
            case PurchaseOrder::PENDING:   // submitted for approval -> tell the approvers
                $this->send(User::approvers()->whereNotNull('email')->pluck('email')->all(), new PurchaseOrderCreated($order));
                break;
            case PurchaseOrder::APPROVED:  // approved -> tell whoever created it
                $creator = $order->createdBy;
                if ($creator && $creator->email) {
                    $this->send([$creator->email], new PurchaseOrderApproved($order));
                }
                break;
            case PurchaseOrder::SENT:      // sent -> e-mail the supplier (if they have an address)
                $supplier = $order->supplier;
                if ($supplier && $supplier->email) {
                    $this->send([$supplier->email], new PurchaseOrderSent($order));
                }
                break;
        }
    }

    public function deleted(PurchaseOrder $order)
    {
        ActivityLog::record($order, 'deleted', $order->status, null, 'Draft order deleted (soft delete).');
        DashboardWidgetService::flush();
    }

    /** A mail problem (bad SMTP settings, no queue worker...) must never block the workflow. */
    protected function send(array $recipients, $mailable): void
    {
        if (empty($recipients)) {
            return;
        }
        try {
            Mail::to($recipients)->send($mailable);
        } catch (Throwable $e) {
            Log::warning('Purchase order e-mail could not be sent: ' . $e->getMessage());
        }
    }
}
