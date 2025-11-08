<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Renda;
use App\Models\Gasto;
use Carbon\Carbon;

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

    public function index()
    {
        [$userId, $redirect] = $this->requireSessionUser();
        if ($redirect) return $redirect;

        return view('analytics');
    }

    public function data(\Illuminate\Http\Request $request)
    {
        // --- sessão ---
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return response()->json([
                'error' => 'unauthenticated'
            ], 401);
        }
        $userId = $sessionUser['id'];

        // --- período ---
        $monthsParam = (int) $request->query('months', 0); // se vier, tem prioridade
        $startYm     = $request->query('start');           // 'YYYY-MM'
        $endYm       = $request->query('end');             // 'YYYY-MM'

        try {
            if ($monthsParam > 0) {
                // últimos N meses (inclui mês atual)
                $end   = \Carbon\Carbon::now()->startOfMonth();
                $start = (clone $end)->subMonths(max(1, $monthsParam) - 1);
            } elseif ($startYm && $endYm) {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $startYm)->startOfMonth();
                $end   = \Carbon\Carbon::createFromFormat('Y-m', $endYm)->startOfMonth();
                if ($start->greaterThan($end)) {
                    [$start, $end] = [$end, $start];
                }
            } else {
                // padrão: 12 meses
                $end   = \Carbon\Carbon::now()->startOfMonth();
                $start = (clone $end)->subMonths(11);
            }
        } catch (\Throwable $e) {
            // fallback seguro
            $end   = \Carbon\Carbon::now()->startOfMonth();
            $start = (clone $end)->subMonths(11);
        }

        // monta sequência de meses ASC
        $months = [];
        $walker = $start->copy();
        while ($walker <= $end) {
            $months[] = $walker->format('Y-m');
            $walker->addMonth();
        }

        // Expressões de ano-mês pelo COALESCE(data, created_at)
        $dateExprR = "DATE_FORMAT(COALESCE(rendas.data, rendas.created_at), '%Y-%m')";
        $dateExprG = "DATE_FORMAT(COALESCE(gastos.data, gastos.created_at), '%Y-%m')";

        // janela de datas real (para WHERE BETWEEN)
        $fromDate = $start->toDateString();
        $toDate   = $end->copy()->endOfMonth()->toDateString();

        // --- consultas agregadas ---
        $rendasMap = \App\Models\Renda::where('user_id', $userId)
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(data, created_at)'), [$fromDate, $toDate])
            ->select(
                \Illuminate\Support\Facades\DB::raw("$dateExprR as ym"),
                \Illuminate\Support\Facades\DB::raw('SUM(valor) as total')
            )
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $gastosMap = \App\Models\Gasto::where('user_id', $userId)
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(data, created_at)'), [$fromDate, $toDate])
            ->select(
                \Illuminate\Support\Facades\DB::raw("$dateExprG as ym"),
                \Illuminate\Support\Facades\DB::raw('SUM(valor) as total')
            )
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // --- monta séries ---
        $labels          = [];
        $rendasSeries    = [];
        $gastosSeries    = [];
        $saldoMesSeries  = [];
        $saldoAcumSeries = [];

        $running = 0.0;
        foreach ($months as $ym) {
            $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('m/Y');

            $r = (float) ($rendasMap[$ym] ?? 0);
            $g = (float) ($gastosMap[$ym] ?? 0);
            $s = $r - $g;

            $running += $s;

            $rendasSeries[]    = $r;
            $gastosSeries[]    = $g;
            $saldoMesSeries[]  = $s;
            $saldoAcumSeries[] = $running;
        }

        // --- totais e breakdown ---
        $totR = array_sum($rendasSeries);
        $totG = array_sum($gastosSeries);
        $totS = $totR - $totG;
        $savingRate = $totR > 0 ? round(($totS / $totR) * 100, 1) : 0.0;

        $months_breakdown = [];
        foreach ($months as $i => $ym) {
            $months_breakdown[] = [
                'label'  => $labels[$i],
                'rendas' => $rendasSeries[$i],
                'gastos' => $gastosSeries[$i],
                'saldo'  => $saldoMesSeries[$i],
            ];
        }

        return response()->json([
            'labels'          => $labels,
            'rendas'          => $rendasSeries,
            'gastos'          => $gastosSeries,
            'saldo_mes'       => $saldoMesSeries,
            'saldo_acumulado' => $saldoAcumSeries,
            'totals'          => [
                'rendas' => $totR,
                'gastos' => $totG,
                'saldo'  => $totS,
                'saving_rate' => $savingRate,
            ],
            'months_breakdown' => $months_breakdown,
            'range' => [
                'start' => $start->format('Y-m'),
                'end'   => $end->format('Y-m'),
            ],
        ]);
    }

}
