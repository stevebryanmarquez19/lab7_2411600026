<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        // Polymorphic audit trail: used for purchase order approval history and supplier delete/restore events.
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('loggable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name')->default('System');
            $table->string('action', 40);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
