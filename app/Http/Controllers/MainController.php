<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class MainController extends Controller
{
   public function index()
{
    $sessionUser = session('user');
    if (!$sessionUser || !isset($sessionUser['id'])) {
        return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
    }

    $user = User::find($sessionUser['id']); // <- envia o usuário completo
    return view('home', compact('user'));
}
}
