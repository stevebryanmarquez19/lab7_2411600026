<?php

namespace App\Services;

use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;

/**
 * Data for the procurement dashboard widgets. Shared by the View Composers (first page load)
 * and the JSON endpoints (AJAX refresh) so both always show the same numbers.
 * Each widget is cached for a short time; the PurchaseOrderObserver clears the cache on changes.
 */
class DashboardWidgetService
{
    public const WIDGETS = ['pending-orders', 'recent-orders', 'supplier-performance', 'reorder-alerts'];

    public static function flush(): void
    {
        foreach (self::WIDGETS as $widget) {
            Cache::forget('widgets.' . $widget);
        }
    }

    protected function remember(string $widget, callable $callback): array
    {
        return Cache::remember('widgets.' . $widget, config('procurement.widget_ttl'), $callback);
    }

    public function pendingOrders(): array
    {
        return $this->remember('pending-orders', function () {
            $pending = PurchaseOrder::pending()->get(['id', 'total']);

            return [
                'pendingCount' => $pending->count(),
                'pendingValue' => round($pending->sum('total'), 2),
                'needsAdminCount' => $pending->filter(function ($order) {
                    return $order->requiresAdminApproval();
                })->count(),
            ];
        });
    }

    public function recentOrders(): array
    {
        return $this->remember('recent-orders', function () {
            $orders = PurchaseOrder::with(['supplier', 'createdBy'])->latest('id')->limit(5)->get();

            return ['recentOrders' => PurchaseOrderResource::collection($orders)->resolve()];
        });
    }

    public function supplierPerformance(): array
    {
        return $this->remember('supplier-performance', function () {
            return ['topSuppliers' => app(SupplierPerformanceService::class)->topSuppliers(5)];
        });
    }

    public function reorderAlerts(): array
    {
        return $this->remember('reorder-alerts', function () {
            return ['reorderAlerts' => app(ReorderService::class)->reorderAlerts()];
        });
    }
}
