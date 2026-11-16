<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            // Not unique at DB level: a soft-deleted supplier must not block re-using its e-mail.
            // Uniqueness among live suppliers is enforced in SupplierController validation.
            $table->string('email');
            $table->string('phone', 30);
            $table->text('address');
            $table->string('payment_terms', 50)->default('Net 30');
            $table->unsignedSmallInteger('lead_time_days')->default(7);
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_name');
            $table->index('email');
            $table->index(['is_active', 'lead_time_days']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
