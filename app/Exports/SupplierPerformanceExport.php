<?php

namespace App\Exports;

use App\Services\SupplierPerformanceService;

class SupplierPerformanceExport extends BaseExport
{
    public function title(): string
    {
        return 'Supplier performance';
    }

    public function headings(): array
    {
        return ['Supplier', 'Orders placed', 'Delivered', 'On time', 'On-time %', 'Avg actual days', 'Avg quoted days', 'Accuracy %', 'Total value'];
    }

    public function rows(): array
    {
        return collect(app(SupplierPerformanceService::class)->allPerformance())->map(function ($r) {
            return [$r['name'], $r['total_orders'], $r['delivered_orders'], $r['on_time_orders'],
                $r['on_time_rate'] ?? 'n/a', $r['avg_actual_days'] ?? 'n/a', $r['avg_quoted_days'] ?? 'n/a',
                $r['order_accuracy'] ?? 'n/a', number_format($r['total_value'], 2)];
        })->all();
    }
}
