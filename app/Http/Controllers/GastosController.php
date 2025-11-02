<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;

class GastosController extends Controller
{
    private function requireSessionUser()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return [null, redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.')];
        }
        return [$sessionUser['id'], null];
    }

    public function create()
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        // página para cadastrar 1..n gastos
        return view('send_expenses');
    }

    public function index()
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $gastos = Gasto::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        return view('show_gastos', compact('gastos'));
    }

    public function store(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $validated = $request->validate([
            'gastos' => 'required|array|min:1',
            'gastos.*.descricao' => 'required|string|max:255',
            'gastos.*.valor' => 'required|numeric|min:0',
            'gastos.*.data' => 'nullable|date',
        ]);

        foreach ($validated['gastos'] as $g) {
            Gasto::create([
                'user_id'   => $userId,
                'descricao' => trim($g['descricao']),
                'valor'     => $g['valor'],
                'data'      => $g['data'] ?? null,
            ]);
        }

        return redirect()->route('gastos.index')->with('success', 'Gastos salvos com sucesso!');
    }

    public function destroy($id)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $gasto = Gasto::where('user_id', $userId)->where('id', $id)->firstOrFail();
        $gasto->delete();

        return back()->with('success', 'Gasto excluído com sucesso!');
    }
}
