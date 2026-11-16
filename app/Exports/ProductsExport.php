<?php

namespace App\Exports;

use App\Services\SearchService;

class ProductsExport extends BaseExport
{
    public function title(): string
    {
        return 'Products list';
    }

    public function headings(): array
    {
        return ['SKU', 'Name', 'Category', 'Quantity', 'Reorder level', 'Supplier', 'Cost price', 'Pack size', 'Min order qty'];
    }

    public function rows(): array
    {
        return app(SearchService::class)->searchProducts($this->filters)->get()->map(function ($p) {
            return [$p->sku, $p->name, $p->category, $p->quantity, $p->reorder_level,
                optional($p->supplier)->company_name ?? '—', number_format($p->cost_price, 2), $p->pack_size, $p->min_order_quantity];
        })->all();
    }
}
