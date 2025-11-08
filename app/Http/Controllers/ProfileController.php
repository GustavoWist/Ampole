<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    private function requireUser()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return [null, redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.')];
        }
        $user = User::find($sessionUser['id']);
        if (!$user) {
            return [null, redirect('/prohibited')->with('loginError', 'Usuário não encontrado.')];
        }
        return [$user, null];
    }

    public function edit()
    {
        [$user, $redir] = $this->requireUser();
        if ($redir) return $redir;

        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        [$user, $redir] = $this->requireUser();
        if ($redir) return $redir;

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users,username,' . $user->id,
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'username.unique' => 'Este nome de usuário já está em uso.',
            'avatar.image'    => 'Envie uma imagem válida.',
            'avatar.mimes'    => 'Formatos aceitos: jpg, jpeg, png, webp.',
            'avatar.max'      => 'Máx. 2MB.',
        ]);

        $user->username = $validated['username'];

        if ($request->hasFile('avatar')) {
            // apaga antigo (se existia)
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public'); // public/storage/avatars/...
            $user->avatar = $path;
        }

        $user->save();

        // Atualiza sessão (mantém nome atualizado no topo)
        session(['user' => ['id' => $user->id, 'username' => $user->username]]);

        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado!');
    }

    public function updatePassword(Request $request)
    {
        [$user, $redir] = $this->requireUser();
        if ($redir) return $redir;

        $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|max:64|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->with('pwdError', 'Senha atual incorreta.')->withInput();
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('profile.edit')->with('pwdSuccess', 'Senha alterada com sucesso!');
    }
}
