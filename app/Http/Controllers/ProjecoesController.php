<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Renda;
use App\Models\Gasto;
use Carbon\Carbon;

class ProjecoesController extends Controller
{
    private function requireUserId()
    {
        $sessionUser = session('user');
        if (!$sessionUser || !isset($sessionUser['id'])) {
            return [null, redirect('/prohibited')->with('loginError', 'Sua sessão expirou. Faça login novamente.')];
        }
        return [$sessionUser['id'], null];
    }

    public function index()
    {
        [$userId, $redirect] = $this->requireUserId();
        if ($redirect) return $redirect;

        return view('projecoes'); // resources/views/projecoes.blade.php
    }

    public function data(Request $request)
    {
        [$userId, $redirect] = $this->requireUserId();
        if ($redirect) return response()->json(['error' => 'unauthenticated'], 401);

        // quantos meses à frente (padrão: 3)
        $ahead = (int) $request->query('months_ahead', 3);
        if ($ahead < 1) $ahead = 3;

        // início = 1º dia do mês seguinte (futuros)
        $start = Carbon::now()->startOfMonth()->addMonth();
        // fim = start + (ahead - 1) meses
        $end   = $start->copy()->addMonths($ahead - 1);

        // sequência de meses (ASC)
        $months = [];
        $walker = $start->copy();
        while ($walker <= $end) {
            $months[] = $walker->format('Y-m');
            $walker->addMonth();
        }

        $fromDate = $start->toDateString();
        $toDate   = $end->copy()->endOfMonth()->toDateString();

        // Somatórios por mês (somente itens com DATA definida e futura dentro do range)
        $rendasMap = Renda::where('user_id', $userId)
            ->whereNotNull('data')
            ->whereBetween('data', [$fromDate, $toDate])
            ->select(DB::raw("DATE_FORMAT(data, '%Y-%m') as ym"), DB::raw('SUM(valor) as total'))
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $gastosMap = Gasto::where('user_id', $userId)
            ->whereNotNull('data')
            ->whereBetween('data', [$fromDate, $toDate])
            ->select(DB::raw("DATE_FORMAT(data, '%Y-%m') as ym"), DB::raw('SUM(valor) as total'))
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels          = [];
        $rendasSeries    = [];
        $gastosSeries    = [];
        $saldoMesSeries  = [];
        $saldoAcumSeries = [];

        $running = 0.0;
        foreach ($months as $ym) {
            $labels[] = Carbon::createFromFormat('Y-m', $ym)->format('m/Y');
            $r = (float) ($rendasMap[$ym] ?? 0);
            $g = (float) ($gastosMap[$ym] ?? 0);
            $s = $r - $g;
            $running += $s;

            $rendasSeries[]    = $r;
            $gastosSeries[]    = $g;
            $saldoMesSeries[]  = $s;
            $saldoAcumSeries[] = $running;
        }

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
