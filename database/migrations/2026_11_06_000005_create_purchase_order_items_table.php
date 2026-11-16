<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot of the product at order time, so the PO stays readable if the product is later removed or renamed.
            $table->string('product_name');
            $table->string('sku', 50)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);             // price agreed at time of order (NOT the product's current cost)
            $table->unsignedInteger('received_quantity')->default(0);
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
}
