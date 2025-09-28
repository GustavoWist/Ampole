<?php

// app/Models/Renda.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importação para a relação

class Renda extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa (Mass Assignable).
     * Essencial para o método Renda::create(...) na Controller.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'origem',
        'valor',
        'is_principal',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'valor' => 'decimal:2', // Garante que o valor seja tratado como decimal com 2 casas
        'is_principal' => 'boolean', // Garante que 0/1 seja tratado como boolean
    ];

    // --- RELACIONAMENTOS ---

    /**
     * Obtém o usuário que possui esta renda.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
