<?php
// 2026_08_12_192531_addrole_to_users.php.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // String, bukan enum, biar nambah role baru nanti gak perlu migration ubah kolom
            $table->string('role', 20)->default('satpam')->after('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
