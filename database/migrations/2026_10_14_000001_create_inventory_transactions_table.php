<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInventoryTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name')->default('System');
            $table->enum('type', ['opening', 'in', 'out', 'adjustment']);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_before')->default(0);
            $table->unsignedInteger('quantity_after')->default(0);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        // MySQL trigger keeps stock updates atomic even if a transaction is inserted outside the web form.
        // The Eloquent observer provides the same behavior on SQLite/other supported local drivers.
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("CREATE TRIGGER inventory_transactions_before_insert BEFORE INSERT ON inventory_transactions FOR EACH ROW BEGIN DECLARE current_qty INT DEFAULT 0; SELECT quantity INTO current_qty FROM products WHERE id = NEW.product_id FOR UPDATE; IF NEW.type = 'out' AND current_qty < NEW.quantity THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not enough stock for this transaction'; END IF; SET NEW.quantity_before = current_qty; IF NEW.type = 'in' THEN SET NEW.quantity_after = current_qty + NEW.quantity; ELSEIF NEW.type = 'out' THEN SET NEW.quantity_after = current_qty - NEW.quantity; ELSEIF NEW.type = 'adjustment' THEN SET NEW.quantity_after = NEW.quantity; ELSEIF NEW.type = 'opening' THEN SET NEW.quantity_before = 0; SET NEW.quantity_after = NEW.quantity; ELSE SET NEW.quantity_after = current_qty; END IF; END");
            DB::unprepared("CREATE TRIGGER inventory_transactions_after_insert AFTER INSERT ON inventory_transactions FOR EACH ROW BEGIN IF NEW.type = 'in' THEN UPDATE products SET quantity = NEW.quantity_after, updated_at = CURRENT_TIMESTAMP WHERE id = NEW.product_id; ELSEIF NEW.type = 'out' OR NEW.type = 'adjustment' THEN UPDATE products SET quantity = NEW.quantity_after, updated_at = CURRENT_TIMESTAMP WHERE id = NEW.product_id; END IF; END");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_after_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_before_insert');
        }
        Schema::dropIfExists('inventory_transactions');
    }
}
