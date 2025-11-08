<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rendas', function (Blueprint $table) {
            $table->date('data')->nullable()->after('valor');
        });

        // Backfill: define "data" = date(created_at) para registros antigos
        DB::table('rendas')->whereNull('data')->update([
            'data' => DB::raw('DATE(created_at)')
        ]);
    }

    public function down(): void
    {
        Schema::table('rendas', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
