<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('/prohibited');
    }

    public function logout()
    {
        session()->forget('user');
        return redirect()->to('/prohibited');
    }

    public function loginSubmit(Request $request)
    {
        // validação
        $request->validate(
            [
                'login-username' => 'required|string',
                'login-password' => 'required|string',
            ],
            [
                'login-username.required' => 'O username é obrigatório',
                'login-password.required' => 'A senha é obrigatória',
            ]
        );

        $username = $request->input('login-username');
        $password = $request->input('login-password');

        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('loginError', 'Usuário ou senha incorretos.');
        }

        // (opcional) atualizar last_login etc.
        $user->save();

        // efetua login via sessão simples
        session([
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
            ],
        ]);

        return redirect()->to('/');
    }

    public function registerSubmit(Request $request)
    {
        // validação do registro
        $validated = $request->validate(
            [
                'username'              => 'required|string|min:3|max:30|alpha_dash|unique:users,username',
                'email'                 => 'required|email|max:255|unique:users,email',
                'password'              => 'required|string|min:8|max:64|confirmed', // exige password_confirmation
            ],
            [
                'username.required' => 'Informe um nome de usuário.',
                'username.alpha_dash' => 'Use apenas letras, números, hífens e underlines.',
                'username.unique'   => 'Este nome de usuário já está em uso.',
                'email.required'    => 'Informe seu e-mail.',
                'email.email'       => 'Forneça um e-mail válido.',
                'email.unique'      => 'Este e-mail já está em uso.',
                'password.required' => 'Informe uma senha.',
                'password.min'      => 'A senha deve ter pelo menos :min caracteres.',
                'password.confirmed'=> 'As senhas não conferem.',
            ]
        );

        // cria o usuário
        $user = User::create([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // loga imediatamente
        session([
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
            ],
        ]);

        return redirect()->to('/')->with('success', 'Conta criada com sucesso!');
    }
}
