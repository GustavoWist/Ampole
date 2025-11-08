<?php

namespace App\Http\Controllers;

use App\Models\Renda;
use App\Models\Gasto;
use Illuminate\Http\Request;

class EconomiesController extends Controller
{
    private function requireSessionUser()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return [null, redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.')];
        }
        return [$sessionUser['id'], null];
    }

    // ---- TELA LIMPA PARA CRIAR NOVAS RENDAS (mensal, etc.)
    public function create()
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        return view('rendas_create'); // resources/views/rendas_create.blade.php
    }

    // ---- SALVAR VÁRIAS RENDAS DE UMA VEZ (NÃO APAGA EXISTENTES)
    public function storeMany(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $validated = $request->validate([
            'rendas'              => 'required|array|min:1',
            'rendas.*.origem'     => 'required|string|max:255',
            'rendas.*.valor'      => 'required|numeric|min:0',
            'rendas.*.data'       => 'nullable|date',
            'rendas.*.principal'  => 'nullable|boolean',
        ]);

        // Cria somente linhas válidas
        foreach ($validated['rendas'] as $r) {
            Renda::create([
                'user_id'      => $userId,
                'origem'       => trim($r['origem']),
                'valor'        => $r['valor'],
                'data'         => $r['data'] ?? null,
                'is_principal' => !empty($r['principal']),
            ]);
        }

        return redirect()->route('economies.show')->with('success', 'Rendas adicionadas com sucesso!');
    }

    // ---- LISTAGEM (com filtro/ordenação)
    public function show(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $sort = $request->query('sort', 'date_desc');

        $query = Renda::where('user_id', $userId);
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

        $rendas = $query->get();

        return view('show_rendas', compact('rendas', 'sort'));
    }

    // ---- EDITAR UMA renda (form separado)
    public function edit($id)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $renda = Renda::where('user_id', $userId)->findOrFail($id);
        return view('renda_edit', compact('renda')); // resources/views/renda_edit.blade.php
    }

    public function update(Request $request, $id)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $validated = $request->validate([
            'origem'     => 'required|string|max:255',
            'valor'      => 'required|numeric|min:0',
            'data'       => 'nullable|date',
            'principal'  => 'nullable|boolean',
        ]);

        $renda = Renda::where('user_id', $userId)->findOrFail($id);
        $renda->update([
            'origem'       => trim($validated['origem']),
            'valor'        => $validated['valor'],
            'data'         => $validated['data'] ?? $renda->data,
            'is_principal' => !empty($validated['principal']),
        ]);

        return redirect()->route('economies.show')->with('success', 'Renda atualizada com sucesso!');
    }

    // ---- EXCLUIR renda
    public function destroy($id)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $renda = Renda::where('user_id', $userId)->where('id', $id)->firstOrFail();
        $renda->delete();

        return redirect()->route('economies.show')->with('success', 'Renda excluída com sucesso!');
    }

    // ---- SALDO
    public function saldo(\Illuminate\Http\Request $request)
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.');
        }
        $userId = $sessionUser['id'];

        // MySQL/MariaDB: expressão de ano-mês pela data escolhida (ou created_at)
        $dateExprR = "DATE_FORMAT(COALESCE(rendas.data, rendas.created_at), '%Y-%m')";
        $dateExprG = "DATE_FORMAT(COALESCE(gastos.data, gastos.created_at), '%Y-%m')";

        // Descobrir todos os meses disponíveis (em rendas e gastos)
        $rendaMonths = \App\Models\Renda::where('user_id', $userId)
            ->select(\Illuminate\Support\Facades\DB::raw("$dateExprR as ym"))
            ->groupBy('ym')
            ->pluck('ym')
            ->toArray();

        $gastoMonths = \App\Models\Gasto::where('user_id', $userId)
            ->select(\Illuminate\Support\Facades\DB::raw("$dateExprG as ym"))
            ->groupBy('ym')
            ->pluck('ym')
            ->toArray();

        $monthsYms = collect($rendaMonths)->merge($gastoMonths)
            ->filter()         // remove nulls
            ->unique()
            ->sortDesc()       // mais recentes primeiro
            ->values();

        $months = $monthsYms->map(function ($ym) {
            try {
                $dt = \Carbon\Carbon::createFromFormat('Y-m', $ym);
                return ['ym' => $ym, 'label' => $dt->format('m/Y')];
            } catch (\Throwable $e) {
                return ['ym' => $ym, 'label' => $ym];
            }
        })->all();

        // ---- Leitura de filtros ----
        $mode = $request->query('mode', 'single'); // single | multi | all
        $selectedMonth  = $request->query('month');       // 'YYYY-MM'
        $selectedMonths = (array) $request->query('months', []);

        // Normaliza seleção com base no que existe no banco
        if ($mode === 'single') {
            if (!$selectedMonth) {
                // Se não veio, usa o mês mais recente existente; se não houver nenhum, usa o mês atual
                $selectedMonth = $monthsYms->first() ?? now()->format('Y-m');
            }
            $selectedYms = [$selectedMonth];
        } elseif ($mode === 'multi') {
            // Mantém só meses válidos
            $selectedYms = collect($selectedMonths)
                ->intersect($monthsYms)
                ->values()
                ->all();

            // Se usuário não selecionou nada mas há meses disponíveis, escolhe o mais recente por padrão
            if (empty($selectedYms) && $monthsYms->isNotEmpty()) {
                $selectedYms = [$monthsYms->first()];
            }
        } else { // all
            $mode = 'all';
            $selectedYms = $monthsYms->all(); // todos os meses existentes
        }

        // Mapas de somas por mês (filtrados conforme o modo)
        $rendasMap = \App\Models\Renda::select(
                \Illuminate\Support\Facades\DB::raw("$dateExprR as ym"),
                \Illuminate\Support\Facades\DB::raw('SUM(valor) as total')
            )
            ->where('user_id', $userId)
            ->when($mode !== 'all', function ($q) use ($selectedYms, $dateExprR) {
                if (!empty($selectedYms)) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw($dateExprR), $selectedYms);
                } else {
                    // nada selecionado -> não traz registros
                    $q->whereRaw('1=0');
                }
            })
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $gastosMap = \App\Models\Gasto::select(
                \Illuminate\Support\Facades\DB::raw("$dateExprG as ym"),
                \Illuminate\Support\Facades\DB::raw('SUM(valor) as total')
            )
            ->where('user_id', $userId)
            ->when($mode !== 'all', function ($q) use ($selectedYms, $dateExprG) {
                if (!empty($selectedYms)) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw($dateExprG), $selectedYms);
                } else {
                    $q->whereRaw('1=0');
                }
            })
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // Decide quais meses mostrar no breakdown (ordem desc)
        $monthsToShow = collect($mode === 'all' ? $monthsYms->all() : $selectedYms)
            ->sortDesc()
            ->values()
            ->all();

        // Monta breakdown e totais
        $breakdown = [];
        $totalRendas = 0.0;
        $totalGastos = 0.0;

        foreach ($monthsToShow as $ym) {
            $r = (float) ($rendasMap[$ym] ?? 0);
            $g = (float) ($gastosMap[$ym] ?? 0);
            $s = $r - $g;

            $totalRendas += $r;
            $totalGastos += $g;

            // label m/Y
            try {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('m/Y');
            } catch (\Throwable $e) {
                $label = $ym;
            }

            $breakdown[] = [
                'ym' => $ym,
                'label' => $label,
                'rendas' => $r,
                'gastos' => $g,
                'saldo'  => $s,
            ];
        }

        $saldo = $totalRendas - $totalGastos;

        return view('saldo', [
            'mode'           => $mode,
            'months'         => $months,        // [{ym, label}, ...]
            'selectedMonth'  => $selectedMonth,
            'selectedMonths' => $selectedYms,   // normalizado
            'totalRendas'    => $totalRendas,
            'totalGastos'    => $totalGastos,
            'saldo'          => $saldo,
            'breakdown'      => $breakdown,
        ]);
    }

}
