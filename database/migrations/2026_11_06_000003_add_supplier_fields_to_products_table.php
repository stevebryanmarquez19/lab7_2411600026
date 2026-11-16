<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('reorder_level')->constrained('suppliers')->nullOnDelete();
            $table->decimal('cost_price', 12, 2)->default(0)->after('supplier_id');      // what we pay the supplier per unit
            $table->unsignedInteger('pack_size')->default(1)->after('cost_price');         // supplier sells in multiples of this
            $table->unsignedInteger('min_order_quantity')->default(1)->after('pack_size'); // supplier minimum order quantity
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'cost_price', 'pack_size', 'min_order_quantity']);
        });
    }
}
