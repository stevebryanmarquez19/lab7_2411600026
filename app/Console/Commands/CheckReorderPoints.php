<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\User;
use App\Services\ReorderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckReorderPoints extends Command
{
    protected $signature = 'inventory:check-reorder {--dry-run : Show what would be ordered without creating purchase orders}';

    protected $description = 'Find products at or below their reorder point and create draft purchase orders per supplier';

    public function handle(ReorderService $service)
    {
        $this->info('Checking reorder points...');

        $needing = $service->getProductsNeedingReorder();
        if ($needing->isEmpty()) {
            $this->info('Nothing to reorder. All stock levels are above their reorder points.');
            return 0;
        }

        $this->table(
            ['SKU', 'Product', 'On hand', 'Reorder point', 'Suggested qty', 'Supplier'],
            $needing->map(function ($p) use ($service) {
                return [$p->sku, $p->name, $p->quantity, $service->calculateReorderPoint($p),
                    $service->calculateOptimalOrderQuantity($p), $p->supplier->company_name];
            })->all()
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run: no purchase orders were created.');
            return 0;
        }

        $orders = $service->generatePurchaseOrders();

        $this->info($orders->count() . ' draft purchase order(s) created:');
        $this->table(
            ['Reference', 'Supplier', 'Items', 'Total'],
            $orders->map(function ($o) {
                return [$o->reference_number, $o->supplier->company_name, $o->items->count(), config('procurement.currency') . number_format($o->total, 2)];
            })->all()
        );

        // Notify administrators (a mail problem must not make the scheduled job fail).
        $admins = User::where('role', 'admin')->whereNotNull('email')->pluck('email')->all();
        if ($admins && $orders->isNotEmpty()) {
            try {
                Mail::to($admins)->send(new LowStockAlert($needing, $orders));
                $this->info('Administrators notified: ' . implode(', ', $admins));
            } catch (\Throwable $e) {
                $this->warn('Orders were created, but the administrator e-mail failed: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
