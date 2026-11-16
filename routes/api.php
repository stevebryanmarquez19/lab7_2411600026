<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryApiController;

Route::get('/dashboard/stats', [InventoryApiController::class, 'summary']);
Route::get('/products/low-stock', [InventoryApiController::class, 'lowStock']);
Route::get('/transactions/recent', [InventoryApiController::class, 'recentTransactions']);
