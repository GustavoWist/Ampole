<?php

// database/migrations/2025_11_02_100000_add_indexes_rendas_gastos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rendas', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
        Schema::table('gastos', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }
    public function down(): void {
        Schema::table('rendas', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};

