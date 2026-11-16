<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // admin | manager | warehouse | staff
            $table->string('role', 20)->default('staff')->after('password')->index();
        });

        // The Lab 6 demo login created this account without a role.
        DB::table('users')->where('email', 'admin@fitpulse.local')->update(['role' => 'admin']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
}
