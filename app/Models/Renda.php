<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Renda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'origem',
        'valor',
        'data',         // ⬅️ novo
        'is_principal',
    ];

    protected $casts = [
        'valor'        => 'decimal:2',
        'data'         => 'date',       // ⬅️ novo
        'is_principal' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
