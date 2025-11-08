<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // ok usar Model simples
// Se preferir Auth do Laravel, troque para: use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];

    // Se sua tabela não tiver created_at/updated_at:
    // public $timestamps = false;
}
