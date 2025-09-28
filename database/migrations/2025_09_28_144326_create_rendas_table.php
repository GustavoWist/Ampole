<?php

// Database/Migrations/YYYY_MM_DD_create_rendas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liga ao usuário
            $table->string('origem'); // Ex: Salário Principal, Freela, Aluguel
            $table->decimal('valor', 10, 2); // Valor da renda
            $table->boolean('is_principal')->default(false); // Flag para identificar a renda principal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendas');
    }
};
