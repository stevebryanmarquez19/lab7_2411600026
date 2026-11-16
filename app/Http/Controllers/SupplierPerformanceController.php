<?php

namespace App\Http\Controllers;

use App\Services\SupplierPerformanceService;
use Illuminate\Support\Facades\Gate;

class SupplierPerformanceController extends Controller
{
    public function index(SupplierPerformanceService $performance)
    {
        Gate::authorize('view-reports');

        return view('reports.supplier-performance', [
            'rows' => $performance->allPerformance(),
            'top' => $performance->topSuppliers(3),
            'bottom' => $performance->bottomSuppliers(3),
            'trend' => $performance->deliveryTrend(6),
        ]);
    }
}
