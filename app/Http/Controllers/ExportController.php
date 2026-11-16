<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\PurchaseOrdersExport;
use App\Exports\SupplierPerformanceExport;
use App\Exports\TransactionsExport;
use App\Support\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    protected $exports = [
        'products' => ProductsExport::class,
        'purchase-orders' => PurchaseOrdersExport::class,
        'transactions' => TransactionsExport::class,
        'supplier-performance' => SupplierPerformanceExport::class,
    ];

    public function download(Request $request, string $type, string $format)
    {
        abort_unless(isset($this->exports[$type]), 404);
        Gate::authorize($type === 'supplier-performance' ? 'view-reports' : 'export-data');

        // The same filters used on the list screens apply to the export.
        $export = new $this->exports[$type]($request->query());

        if ($format === 'pdf') {
            if (!Pdf::available()) {
                return back()->with('error', 'PDF export needs the dompdf package. Run: composer require barryvdh/laravel-dompdf');
            }

            return $export->toPdf();
        }

        return $export->toCsv();
    }
}
