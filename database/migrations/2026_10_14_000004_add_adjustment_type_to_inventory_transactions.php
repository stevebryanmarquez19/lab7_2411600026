<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdjustmentTypeToInventoryTransactions extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY type ENUM('opening','in','out','adjustment') NOT NULL");
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_before_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_after_insert');
            DB::unprepared("CREATE TRIGGER inventory_transactions_before_insert BEFORE INSERT ON inventory_transactions FOR EACH ROW BEGIN DECLARE current_qty INT DEFAULT 0; SELECT quantity INTO current_qty FROM products WHERE id = NEW.product_id FOR UPDATE; IF NEW.type = 'out' AND current_qty < NEW.quantity THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not enough stock for this transaction'; END IF; SET NEW.quantity_before = current_qty; IF NEW.type = 'in' THEN SET NEW.quantity_after = current_qty + NEW.quantity; ELSEIF NEW.type = 'out' THEN SET NEW.quantity_after = current_qty - NEW.quantity; ELSEIF NEW.type = 'adjustment' THEN SET NEW.quantity_after = NEW.quantity; ELSEIF NEW.type = 'opening' THEN SET NEW.quantity_before = 0; SET NEW.quantity_after = NEW.quantity; ELSE SET NEW.quantity_after = current_qty; END IF; END");
            DB::unprepared("CREATE TRIGGER inventory_transactions_after_insert AFTER INSERT ON inventory_transactions FOR EACH ROW BEGIN IF NEW.type = 'in' OR NEW.type = 'out' OR NEW.type = 'adjustment' THEN UPDATE products SET quantity = NEW.quantity_after, updated_at = CURRENT_TIMESTAMP WHERE id = NEW.product_id; END IF; END");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_after_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_before_insert');
            DB::statement("ALTER TABLE inventory_transactions MODIFY type ENUM('opening','in','out') NOT NULL");
        }
    }
}
