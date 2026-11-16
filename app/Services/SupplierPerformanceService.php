<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;

/**
 * Supplier delivery metrics. Results are cached (query caching) and cleared whenever an order is received.
 */
class SupplierPerformanceService
{
    protected const ALL_KEY = 'supplier_performance_all';

    protected static function key(int $supplierId): string
    {
        return 'supplier_performance_' . $supplierId;
    }

    public static function forget(?int $supplierId = null): void
    {
        if ($supplierId) {
            Cache::forget(self::key($supplierId));
        }
        Cache::forget(self::ALL_KEY);
        Cache::forget('supplier_performance_trend');
    }

    public function calculatePerformance(Supplier $supplier): array
    {
        return Cache::remember(self::key($supplier->id), config('procurement.cache_ttl'), function () use ($supplier) {
            // Eager load items once instead of one query per order.
            $placed = $supplier->purchaseOrders()
                ->with('items')
                ->whereIn('status', [PurchaseOrder::SENT, PurchaseOrder::PARTIAL, PurchaseOrder::RECEIVED])
                ->get();

            $delivered = $placed->filter(function ($order) {
                return $order->status === PurchaseOrder::RECEIVED && $order->delivered_at;
            });

            $onTime = $delivered->filter(function ($order) {
                return $order->expected_delivery_date
                    && $order->delivered_at->copy()->startOfDay()->lte($order->expected_delivery_date);
            });

            $avgActual = $delivered->avg(function ($order) {
                return $order->order_date->diffInDays($order->delivered_at->copy()->startOfDay());
            });
            $avgQuoted = $delivered->filter(function ($order) {
                return (bool) $order->expected_delivery_date;
            })->avg(function ($order) {
                return $order->order_date->diffInDays($order->expected_delivery_date);
            });

            $items = $delivered->flatMap(function ($order) {
                return $order->items;
            });
            $accurate = $items->filter(function ($item) {
                return $item->received_quantity === $item->quantity;
            });

            return [
                'supplier_id' => $supplier->id,
                'total_orders' => $placed->count(),
                'delivered_orders' => $delivered->count(),
                'on_time_orders' => $onTime->count(),
                'on_time_rate' => $delivered->count() ? round($onTime->count() / $delivered->count() * 100, 1) : null,
                'avg_actual_days' => $avgActual !== null ? round($avgActual, 1) : null,
                'avg_quoted_days' => $avgQuoted !== null ? round($avgQuoted, 1) : null,
                'order_accuracy' => $items->count() ? round($accurate->count() / $items->count() * 100, 1) : null,
                'total_value' => round($placed->sum('total'), 2),
            ];
        });
    }

    /** One row per (non-deleted) supplier, ready for the report table and exports. */
    public function allPerformance(): array
    {
        return Cache::remember(self::ALL_KEY, config('procurement.cache_ttl'), function () {
            return Supplier::orderBy('company_name')->get()->map(function ($supplier) {
                return array_merge([
                    'name' => $supplier->company_name,
                    'is_active' => $supplier->is_active,
                    'quoted_lead_time' => $supplier->lead_time_days,
                ], $this->calculatePerformance($supplier));
            })->all();
        });
    }

    /** Suppliers with at least one delivery, best first. */
    public function topSuppliers(int $limit = 5): array
    {
        return $this->ranked()->take($limit)->values()->all();
    }

    public function bottomSuppliers(int $limit = 3): array
    {
        return $this->ranked()->reverse()->take($limit)->values()->all();
    }

    protected function ranked()
    {
        return collect($this->allPerformance())
            ->filter(function ($row) {
                return $row['delivered_orders'] > 0;
            })
            ->sortByDesc(function ($row) {
                return [$row['on_time_rate'], $row['order_accuracy']];
            })
            ->values();
    }

    /** Monthly delivery trend for the last N months (on-time vs late deliveries). */
    public function deliveryTrend(int $months = 6): array
    {
        return Cache::remember('supplier_performance_trend', config('procurement.cache_ttl'), function () use ($months) {
            $start = now()->subMonths($months - 1)->startOfMonth();
            $orders = PurchaseOrder::whereNotNull('delivered_at')
                ->whereNotNull('expected_delivery_date')
                ->where('delivered_at', '>=', $start)
                ->get();

            $labels = $onTime = $late = $avgDays = [];
            for ($i = 0; $i < $months; $i++) {
                $month = $start->copy()->addMonths($i);
                $inMonth = $orders->filter(function ($order) use ($month) {
                    return $order->delivered_at->format('Y-m') === $month->format('Y-m');
                });
                $ok = $inMonth->filter(function ($order) {
                    return $order->delivered_at->copy()->startOfDay()->lte($order->expected_delivery_date);
                })->count();

                $labels[] = $month->format('M Y');
                $onTime[] = $ok;
                $late[] = $inMonth->count() - $ok;
                $avgDays[] = $inMonth->count()
                    ? round($inMonth->avg(function ($order) {
                        return $order->order_date->diffInDays($order->delivered_at->copy()->startOfDay());
                    }), 1)
                    : 0;
            }

            return ['labels' => $labels, 'on_time' => $onTime, 'late' => $late, 'avg_days' => $avgDays];
        });
    }
}
