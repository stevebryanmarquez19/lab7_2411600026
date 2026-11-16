<?php

namespace App\Providers;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        PurchaseOrder::class => PurchaseOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Gates: simple role checks that are not tied to one model instance.
        Gate::define('view-suppliers', function (User $user) {
            return $user->hasRole(...User::ROLES);
        });
        Gate::define('manage-suppliers', function (User $user) {
            return $user->hasRole('admin', 'manager');
        });
        Gate::define('restore-suppliers', function (User $user) {
            return $user->isAdmin();
        });
        Gate::define('generate-orders', function (User $user) {
            return $user->hasRole('admin', 'manager');
        });
        Gate::define('view-reports', function (User $user) {
            return $user->hasRole('admin', 'manager');
        });
        Gate::define('export-data', function (User $user) {
            return $user->hasRole('admin', 'manager', 'staff');
        });
    }
}
