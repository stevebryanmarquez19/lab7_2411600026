<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\InventoryTransaction::observe(\App\Observers\InventoryTransactionObserver::class);
        \App\Models\PurchaseOrder::observe(\App\Observers\PurchaseOrderObserver::class);
    }
}
