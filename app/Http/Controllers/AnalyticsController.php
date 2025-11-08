<?php

namespace App\Http\Controllers;

use App\Models\Renda;
use App\Models\Gasto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    private function requireSessionUser()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return [null, redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.')];
        }
        return [$sessionUser['id'], null];
    }

    private function buildPayload(int $userId, int $months = 12): array
    {
        $months = max(1, min(36, $months));
        $end   = Carbon::now()->endOfMonth();
        $start = (clone $end)->subMonths($months - 1)->startOfMonth();

        // ---- AGRUPAMENTO POR MÊS USANDO A "DATA ESCOLHIDA" ----
        // MySQL / MariaDB:
        $dateExprR = "DATE_FORMAT(COALESCE(rendas.data, rendas.created_at), '%Y-%m')";
        $dateExprG = "DATE_FORMAT(COALESCE(gastos.data, gastos.created_at), '%Y-%m')";
        $betweenR  = DB::raw('COALESCE(`rendas`.`data`, `rendas`.`created_at`)');
        $betweenG  = DB::raw('COALESCE(`gastos`.`data`, `gastos`.`created_at`)');

        // SQLite (se usar):     $dateExprR = "strftime('%Y-%m', COALESCE(rendas.data, rendas.created_at))";
        //                       $dateExprG = "strftime('%Y-%m', COALESCE(gastos.data, gastos.created_at))";
        //                       $betweenR  = DB::raw('COALESCE(rendas.data, rendas.created_at)');
        //                       $betweenG  = DB::raw('COALESCE(gastos.data, gastos.created_at)');
        //
        // Postgres (se usar):   $dateExprR = "to_char(COALESCE(rendas.data, rendas.created_at), 'YYYY-MM')";
        //                       $dateExprG = "to_char(COALESCE(gastos.data, gastos.created_at), 'YYYY-MM')";
        //                       $betweenR  = DB::raw('COALESCE(rendas.data, rendas.created_at)');
        //                       $betweenG  = DB::raw('COALESCE(gastos.data, gastos.created_at)');

        $cacheKey = "analytics:{$userId}:{$start->format('Y-m')}:{$end->format('Y-m')}";

        return Cache::remember($cacheKey, 60, function () use ($userId, $start, $end, $dateExprR, $dateExprG, $betweenR, $betweenG) {

            $rendas = Renda::select(DB::raw("$dateExprR as ym"), DB::raw('SUM(valor) as total'))
                ->where('user_id', $userId)
                ->whereBetween($betweenR, [$start, $end])
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');

            $gastos = Gasto::select(DB::raw("$dateExprG as ym"), DB::raw('SUM(valor) as total'))
                ->where('user_id', $userId)
                ->whereBetween($betweenG, [$start, $end])
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');

            // Monta o eixo mensal e datasets
            $labels = [];
            $dsR = [];
            $dsG = [];
            $dsS = [];
            $monthsBreakdown = [];

            foreach (CarbonPeriod::create($start, '1 month', $end) as $m) {
                $ym = $m->format('Y-m');
                $label = $m->format('m/Y');
                $labels[] = $label;

                $r = (float) ($rendas[$ym] ?? 0);
                $g = (float) ($gastos[$ym] ?? 0);
                $s = $r - $g;

                $dsR[] = $r;
                $dsG[] = $g;
                $dsS[] = $s;

                $monthsBreakdown[] = [
                    'ym'    => $ym,
                    'label' => $label,
                    'rendas'=> $r,
                    'gastos'=> $g,
                    'saldo' => $s,
                ];
            }

            $totalRendas = array_sum($dsR);
            $totalGastos = array_sum($dsG);
            $saldoTotal  = $totalRendas - $totalGastos;
            $savingRate  = $totalRendas > 0 ? round(($saldoTotal / $totalRendas) * 100, 1) : 0;

            return [
                'labels' => $labels,
                'datasets' => [
                    'rendas' => $dsR,
                    'gastos' => $dsG,
                    'saldo'  => $dsS,
                ],
                'totals' => [
                    'rendas' => $totalRendas,
                    'gastos' => $totalGastos,
                    'saldo'  => $saldoTotal,
                    'saving_rate' => $savingRate,
                ],
                'months_breakdown' => $monthsBreakdown, // 👈 resumo mês a mês
            ];
        });
    }

    public function index(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $months = (int) $request->query('months', 12);
        $initialPayload = $this->buildPayload($userId, $months);

        return view('analytics', compact('initialPayload'));
    }

    public function data(Request $request)
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        $months = (int) $request->query('months', 12);
        $payload = $this->buildPayload($userId, $months);

        return response()->json($payload);
    }
}
