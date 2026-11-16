<?php

namespace App\Http\Controllers;

use App\Services\DashboardWidgetService;

/**
 * JSON endpoints behind the procurement widgets (polled by AJAX every 30 seconds).
 * They return exactly what the View Composers give the Blade partials.
 */
class DashboardApiController extends Controller
{
    protected function respond(array $data)
    {
        return response()->json(array_merge($data, ['generated_at' => now()->toIso8601String()]));
    }

    public function pendingOrders(DashboardWidgetService $widgets)
    {
        return $this->respond($widgets->pendingOrders());
    }

    public function recentOrders(DashboardWidgetService $widgets)
    {
        return $this->respond($widgets->recentOrders());
    }

    public function supplierPerformance(DashboardWidgetService $widgets)
    {
        return $this->respond($widgets->supplierPerformance());
    }

    public function reorderAlerts(DashboardWidgetService $widgets)
    {
        return $this->respond($widgets->reorderAlerts());
    }
}
