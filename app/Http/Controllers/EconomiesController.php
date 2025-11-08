<?php

namespace App\Http\Controllers;

use App\Models\Renda;
use App\Models\Gasto;
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
            'rendas.*.valor'  => 'required|numeric|min:0',
            'rendas.*.data'   => 'nullable|date',
        ]);

        $userId = $sessionUser['id'];

        // IDs enviados do formulário (para não apagar o que existe)
        $enviadasIds = collect($request->input('rendas'))
            ->pluck('id')
            ->filter()
            ->toArray();

        // Deleta apenas rendas removidas da tela
        Renda::where('user_id', $userId)
            ->whereNotIn('id', $enviadasIds)
            ->delete();

        // Atualiza ou cria
        foreach ($request->input('rendas') as $index => $rendaData) {
            if (!empty($rendaData['id'])) {
                $renda = Renda::where('user_id', $userId)
                    ->where('id', $rendaData['id'])
                    ->first();

                if ($renda) {
                    $renda->update([
                        'origem' => trim($rendaData['origem']),
                        'valor'  => $rendaData['valor'],
                        'data'   => $rendaData['data'] ?? $renda->data,
                    ]);
                    continue;
                }
            }

            Renda::create([
                'user_id'      => $userId,
                'origem'       => trim($rendaData['origem']),
                'valor'        => $rendaData['valor'],
                'data'         => $rendaData['data'] ?? null,
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

        // Ordena pela data escolhida (se houver), senão created_at
        $rendas = Renda::where('user_id', $userId)
            ->orderByRaw('COALESCE(`data`, `created_at`) DESC')
            ->get();

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

    public function saldo()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }

        $userId = $sessionUser['id'];

        $totalRendas = (float) Renda::where('user_id', $userId)->sum('valor');
        $totalGastos = (float) Gasto::where('user_id', $userId)->sum('valor');
        $saldo = $totalRendas - $totalGastos;

        return view('saldo', compact('totalRendas', 'totalGastos', 'saldo'));
    }
}
