<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers'); // suppliers are soft-deleted, so history stays intact
            // draft, pending, approved, rejected, sent, partial, received, cancelled
            $table->string('status', 20)->default('draft');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->dateTime('delivered_at')->nullable();   // set when the order is completed; drives on-time metrics
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('auto_generated')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'order_date']);
            $table->index('order_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
