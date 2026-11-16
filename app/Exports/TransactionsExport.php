<?php

namespace App\Exports;

use App\Services\SearchService;

class TransactionsExport extends BaseExport
{
    public function title(): string
    {
        return 'Inventory transactions';
    }

    public function headings(): array
    {
        return ['Date', 'SKU', 'Product', 'Type', 'Quantity', 'Before', 'After', 'Recorded by', 'Reference'];
    }

    public function rows(): array
    {
        return app(SearchService::class)->searchTransactions($this->filters)->get()->map(function ($t) {
            return [optional($t->created_at)->format('Y-m-d H:i'), optional($t->product)->sku, optional($t->product)->name ?? 'Deleted product',
                strtoupper($t->type), $t->quantity, $t->quantity_before, $t->quantity_after, $t->actor_name, $t->reference];
        })->all();
    }
}
