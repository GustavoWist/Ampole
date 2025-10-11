<?php

namespace App\Http\Controllers;

use App\Models\Renda;
use Illuminate\Http\Request;

class EconomiesController extends Controller
{
    public function store(Request $request)
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }

        $validated = $request->validate([
            'rendas.*.origem' => 'required|string|max:255',
            'rendas.*.valor' => 'required|numeric|min:0',
        ]);

        $userId = $sessionUser['id'];

        // IDs enviados do formulário
        $enviadasIds = collect($request->input('rendas'))
            ->pluck('id')
            ->filter()
            ->toArray();

        // Deleta apenas rendas que não vieram no formulário (usuário removeu manualmente)
        Renda::where('user_id', $userId)
            ->whereNotIn('id', $enviadasIds)
            ->delete();

        // Atualiza ou cria novas rendas
        foreach ($request->input('rendas') as $index => $rendaData) {
            if (isset($rendaData['id'])) {
                // Atualizar existente
                $renda = Renda::where('user_id', $userId)
                    ->where('id', $rendaData['id'])
                    ->first();

                if ($renda) {
                    $renda->update([
                        'origem' => trim($rendaData['origem']),
                        'valor' => $rendaData['valor'],
                    ]);
                    continue;
                }
            }

            // Criar nova renda
            Renda::create([
                'user_id' => $userId,
                'origem' => trim($rendaData['origem']),
                'valor' => $rendaData['valor'],
                'is_principal' => $index === 0,
            ]);
        }

        return redirect()->route('economies.show')->with('success', 'Rendas atualizadas com sucesso!');
    }


    public function show()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }

        $userId = $sessionUser['id'];
        $rendas = Renda::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        return view('show_rendas', compact('rendas'));
    }

    public function edit()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }

        $userId = $sessionUser['id'];
        $rendas = Renda::where('user_id', $userId)->orderBy('is_principal', 'desc')->get();

        return view('send_values', compact('rendas'));
    }

    public function destroy($id)
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }

        $userId = $sessionUser['id'];
        $renda = Renda::where('user_id', $userId)->where('id', $id)->firstOrFail();

        $renda->delete();

        return redirect()->route('economies.show')->with('success', 'Renda excluída com sucesso!');
    }
}
