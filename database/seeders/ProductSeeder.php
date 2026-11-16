<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Morning Run (5km)', 'category' => 'Cardio', 'unit_price' => 15, 'quantity' => 20, 'reorder_level' => 5, 'status' => 'Completed', 'sku' => 'FIT-001', 'date' => '2026-09-08'],
            ['name' => 'Chest & Biceps Strength', 'category' => 'Strength', 'unit_price' => 25, 'quantity' => 12, 'reorder_level' => 10, 'status' => 'Completed', 'sku' => 'FIT-002', 'date' => '2026-09-07'],
            ['name' => 'HIIT Cardio Session', 'category' => 'Cardio', 'unit_price' => 20, 'quantity' => 3, 'reorder_level' => 5, 'status' => 'Incomplete', 'sku' => 'FIT-003', 'date' => '2026-09-06'],
            ['name' => 'Beach Volleyball Match', 'category' => 'Sports', 'unit_price' => 30, 'quantity' => 15, 'reorder_level' => 8, 'status' => 'Completed', 'sku' => 'FIT-004', 'date' => '2026-09-05'],
            ['name' => 'Leg Day Gym Workout', 'category' => 'Strength', 'unit_price' => 35, 'quantity' => 2, 'reorder_level' => 5, 'status' => 'Incomplete', 'sku' => 'FIT-005', 'date' => '2026-09-04'],
            ['name' => 'Evening Cycling Session', 'category' => 'Cardio', 'unit_price' => 18, 'quantity' => 22, 'reorder_level' => 5, 'status' => 'Completed', 'sku' => 'FIT-006', 'date' => '2026-09-03']
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}