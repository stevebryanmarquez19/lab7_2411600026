<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference_number,
            'supplier' => optional($this->supplier)->company_name,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_badge' => $this->status_badge,
            'total' => $this->total,
            'total_formatted' => config('procurement.currency') . number_format($this->total, 2),
            'order_date' => optional($this->order_date)->toDateString(),
            'expected_delivery_date' => optional($this->expected_delivery_date)->toDateString(),
            'created_by' => optional($this->createdBy)->name ?? 'System (auto)',
            'url' => route('purchase-orders.show', $this->id),
        ];
    }
}
