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

        return view('send_expenses');
    }

    public function index(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $sort = $request->query('sort', 'date_desc');

        $query = Gasto::where('user_id', $userId);

        switch ($sort) {
            case 'date_asc':
                $query->orderByRaw('COALESCE(`data`, `created_at`) ASC');
                break;
            case 'valor_asc':
                $query->orderBy('valor', 'asc');
                break;
            case 'valor_desc':
                $query->orderBy('valor', 'desc');
                break;
            case 'date_desc':
            default:
                $query->orderByRaw('COALESCE(`data`, `created_at`) DESC');
                break;
        }

        $gastos = $query->get();

        return view('show_gastos', compact('gastos', 'sort'));
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
