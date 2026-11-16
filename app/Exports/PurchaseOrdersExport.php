<?php

namespace App\Exports;

use App\Services\SearchService;

class PurchaseOrdersExport extends BaseExport
{
    public function title(): string
    {
        return 'Purchase orders';
    }

    public function headings(): array
    {
        return ['Reference', 'Supplier', 'Status', 'Order date', 'Expected delivery', 'Subtotal', 'Tax', 'Total', 'Created by'];
    }

    public function rows(): array
    {
        return app(SearchService::class)->searchPurchaseOrders($this->filters)->get()->map(function ($o) {
            return [$o->reference_number, optional($o->supplier)->company_name, $o->status_label,
                optional($o->order_date)->toDateString(), optional($o->expected_delivery_date)->toDateString(),
                number_format($o->subtotal, 2), number_format($o->tax_amount, 2), number_format($o->total, 2),
                optional($o->createdBy)->name ?? 'System (auto)'];
        })->all();
    }
}
