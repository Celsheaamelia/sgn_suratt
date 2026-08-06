<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_default');
            }
        });

        // Template yang SUDAH ADA sebelum fitur ini dibuat otomatis dianggap
        // published - supaya kontrak yang sudah jalan pakai template lama
        // tidak tiba-tiba kekunci downloadnya. Yang kena "Draft" cuma
        // template yang diupload SETELAH migration ini jalan.
        DB::table('templates')->whereNull('published_at')->update(['published_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });
    }
};
