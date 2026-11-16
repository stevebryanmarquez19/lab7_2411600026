<?php

namespace App\Providers;

use App\Services\DashboardWidgetService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    /**
     * View composers: every time one of these widget partials renders, its data is bound
     * automatically, so no controller has to remember to pass it.
     */
    public function boot()
    {
        View::composer('widgets.pending-orders', function ($view) {
            $view->with(app(DashboardWidgetService::class)->pendingOrders());
        });

        View::composer('widgets.recent-orders', function ($view) {
            $view->with(app(DashboardWidgetService::class)->recentOrders());
        });

        View::composer('widgets.supplier-performance', function ($view) {
            $view->with(app(DashboardWidgetService::class)->supplierPerformance());
        });

        View::composer('widgets.reorder-alerts', function ($view) {
            $view->with(app(DashboardWidgetService::class)->reorderAlerts());
        });
    }
}
