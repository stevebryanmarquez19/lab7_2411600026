<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for Lab 7: role users, suppliers, product-supplier links, 30 days of sales
 * (so the reorder point has real usage to work with) and some completed purchase orders
 * (so the supplier performance report has something to show).
 * Safe to run more than once.   php artisan db:seed --class=ProcurementSeeder
 */
class ProcurementSeeder extends Seeder
{
    public function run()
    {
        $this->users();
        $suppliers = $this->suppliers();
        $this->linkProducts($suppliers);
        $this->salesHistory();
        $this->purchaseHistory($suppliers);
    }

    protected function users(): void
    {
        foreach ([
            ['Admin', 'admin@fitpulse.local', 'admin'],
            ['Maria Manager', 'manager@fitpulse.local', 'manager'],
            ['Wally Warehouse', 'warehouse@fitpulse.local', 'warehouse'],
            ['Sam Staff', 'staff@fitpulse.local', 'staff'],
        ] as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->name = $user->name ?: $name;
            $user->password = $user->password ?: Hash::make('password123');
            $user->role = $role;
            $user->save();
        }
    }

    protected function suppliers(): array
    {
        $rows = [
            'power' => ['PowerGear Fitness Supply', 'Ramon Dela Cruz', 'orders@powergear.test', '+63 82 221 4455', 'Lanang, Davao City', 'Net 30', 5, 5000, true],
            'mindanao' => ['Mindanao Sports Wholesale', 'Liza Santos', 'sales@mindanaosports.test', '+63 82 305 7788', 'Agdao, Davao City', 'Net 15', 7, 3000, true],
            'island' => ['Island Active Distributors', 'Paolo Reyes', 'hello@islandactive.test', '0917-555-2020', 'Toril, Davao City', 'COD', 10, 2000, true],
            'budget' => ['Budget Gym Traders', 'Ana Villanueva', 'info@budgetgym.test', '0928-444-3131', 'Panabo City, Davao del Norte', 'Prepaid', 14, 1000, false],
        ];
        $out = [];
        foreach ($rows as $key => [$company, $contact, $email, $phone, $address, $terms, $lead, $min, $active]) {
            $out[$key] = Supplier::firstOrCreate(['email' => $email], [
                'company_name' => $company, 'contact_person' => $contact, 'phone' => $phone, 'address' => $address,
                'payment_terms' => $terms, 'lead_time_days' => $lead, 'minimum_order_amount' => $min, 'is_active' => $active,
            ]);
        }

        return $out;
    }

    protected function linkProducts(array $s): void
    {
        // sku => [supplier, cost, pack size, minimum order quantity]
        foreach ([
            'FIT-001' => ['power', 250, 5, 10], 'FIT-006' => ['power', 180, 1, 5],
            'FIT-002' => ['mindanao', 420, 1, 5], 'FIT-005' => ['mindanao', 560, 2, 4],
            'FIT-003' => ['island', 300, 1, 5], 'FIT-004' => ['island', 350, 5, 10],
        ] as $sku => [$supplier, $cost, $pack, $min]) {
            Product::where('sku', $sku)->update([
                'supplier_id' => $s[$supplier]->id, 'cost_price' => $cost, 'pack_size' => $pack, 'min_order_quantity' => $min,
            ]);
        }
    }

    /** Net-zero stock movements (an "in" followed by "out"s) back-dated into the last 30 days. */
    protected function salesHistory(): void
    {
        if (InventoryTransaction::where('actor_name', 'Demo seed')->exists()) {
            return;
        }

        // sku => units sold over the last 30 days
        foreach (['FIT-001' => 20, 'FIT-002' => 30, 'FIT-003' => 12, 'FIT-004' => 24, 'FIT-005' => 10, 'FIT-006' => 10] as $sku => $total) {
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                continue;
            }
            $make = function ($type, $qty, $daysAgo, $note) use ($product) {
                $tx = InventoryTransaction::create([
                    'product_id' => $product->id, 'actor_name' => 'Demo seed', 'type' => $type,
                    'quantity' => $qty, 'reference' => 'DEMO', 'notes' => $note,
                ]);
                $tx->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->saveQuietly();
            };

            $make('in', $total, 30, 'Demo seed: stock to cover the sales below.');
            $parts = 6;
            for ($i = 0; $i < $parts; $i++) {
                $make('out', (int) ($total / $parts) + ($i === 0 ? $total % $parts : 0), 28 - $i * 4, 'Demo seed: sales.');
            }
        }
    }

    protected function purchaseHistory(array $s): void
    {
        if (PurchaseOrder::withTrashed()->exists()) {
            return;
        }
        $admin = User::where('email', 'admin@fitpulse.local')->first();
        $manager = User::where('email', 'manager@fitpulse.local')->first();

        // [supplier, days ago ordered, quoted days, actual days (null = still open), status, [sku => [qty ordered, qty received]]]
        $history = [
            ['power', 80, 5, 4, 'received', ['FIT-001' => [20, 20], 'FIT-006' => [10, 10]]],
            ['power', 55, 5, 5, 'received', ['FIT-001' => [15, 15]]],
            ['power', 30, 5, 8, 'received', ['FIT-006' => [10, 8]]],
            ['mindanao', 75, 7, 6, 'received', ['FIT-002' => [12, 12], 'FIT-005' => [6, 6]]],
            ['mindanao', 45, 7, 11, 'received', ['FIT-002' => [10, 10]]],
            ['island', 70, 10, 15, 'received', ['FIT-003' => [10, 10], 'FIT-004' => [10, 10]]],
            ['island', 35, 10, 9, 'received', ['FIT-004' => [10, 10]]],
            ['mindanao', 6, 7, null, 'sent', ['FIT-002' => [8, 0]]],
        ];

        PurchaseOrder::withoutEvents(function () use ($history, $s, $admin, $manager) {
            foreach ($history as [$sup, $daysAgo, $quoted, $actual, $status, $lines]) {
                $ordered = today()->subDays($daysAgo);
                $order = PurchaseOrder::create([
                    'reference_number' => PurchaseOrder::generateReferenceNumber($ordered),
                    'supplier_id' => $s[$sup]->id,
                    'status' => $status,
                    'order_date' => $ordered,
                    'expected_delivery_date' => $ordered->copy()->addDays($quoted),
                    'delivered_at' => $actual !== null ? $ordered->copy()->addDays($actual)->setTime(10, 0) : null,
                    'notes' => 'Demo purchase history.',
                    'terms' => 'Payment terms: ' . $s[$sup]->payment_terms,
                    'created_by' => optional($manager)->id,
                    'approved_by' => optional($admin)->id,
                    'approved_at' => $ordered->copy()->setTime(14, 0),
                    'sent_at' => $ordered->copy()->setTime(15, 0),
                ]);
                foreach ($lines as $sku => [$qty, $got]) {
                    $p = Product::where('sku', $sku)->first();
                    $order->items()->create(['product_id' => $p->id, 'product_name' => $p->name, 'sku' => $sku,
                        'quantity' => $qty, 'unit_price' => $p->cost_price, 'received_quantity' => $got]);
                }
                $order->calculateTotal();
            }
        });
    }
}
