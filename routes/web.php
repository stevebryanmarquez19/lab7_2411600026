<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\InventoryApiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierPerformanceController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['fitpulse.auth'])->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('dashboard');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/export/csv', [ProductController::class, 'export'])->name('products.export');
    Route::get('/inventory/transactions', [InventoryTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/inventory/transactions/{transaction}', [InventoryTransactionController::class, 'show'])->name('transactions.show');
    Route::post('/inventory/transactions', [InventoryTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/api/inventory/summary', [InventoryApiController::class, 'summary'])->name('api.inventory.summary');

    // ---- Lab 7: suppliers ----
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

    // ---- Lab 7: purchase orders ----
    Route::post('/purchase-orders/generate', [PurchaseOrderController::class, 'generate'])->name('purchase-orders.generate');
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchase_order}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
    Route::post('/purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    Route::post('/purchase-orders/{purchase_order}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
    Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::get('/purchase-orders/{purchase_order}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');

    // ---- Lab 7: reports and exports ----
    Route::get('/reports/supplier-performance', [SupplierPerformanceController::class, 'index'])->name('reports.supplier-performance');
    Route::get('/exports/{type}/{format}', [ExportController::class, 'download'])->where('format', 'csv|pdf')->name('exports.download');

    // ---- Lab 7: dashboard widget data (session-protected JSON for AJAX refresh) ----
    Route::get('/api/dashboard/pending-orders', [DashboardApiController::class, 'pendingOrders'])->name('api.dashboard.pending-orders');
    Route::get('/api/dashboard/recent-orders', [DashboardApiController::class, 'recentOrders'])->name('api.dashboard.recent-orders');
    Route::get('/api/dashboard/supplier-performance', [DashboardApiController::class, 'supplierPerformance'])->name('api.dashboard.supplier-performance');
    Route::get('/api/dashboard/reorder-alerts', [DashboardApiController::class, 'reorderAlerts'])->name('api.dashboard.reorder-alerts');
});
