<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #30373F; }
        .card { border-radius: 14px; }
        .stat { font-size: 1.3rem; }
        .table-sm td, .table-sm th { padding: .45rem .6rem; }
        .text-muted-invert { color: #cbd5e1; }
        .year-badge { font-weight: 600; color: #e5e7eb; }
        .chip { border-radius: 9999px; } /* pill */
        /* Chips (bolinhas) — estado padrão (desmarcado) */
        .btn-chip {
        border-radius: 9999px;             /* formato pílula */
        background-color: #1f2730;         /* fundo escuro */
        color: #e5e7eb;                    /* texto claro */
        border: 1px solid #3a4654;         /* borda sutil */
        transition: background-color .15s, color .15s, border-color .15s, box-shadow .15s;
        }

        /* Hover no desmarcado */
        .btn-chip:hover {
        background-color: #27313c;
        border-color: #4a5665;
        color: #ffffff;
        }

        /* Foco (acessibilidade) */
        .btn-check:focus + .btn-chip,
        .btn-chip:focus {
        box-shadow: 0 0 0 .2rem rgba(255,255,255,.15);
        }

        /* Estado marcado (invertido) */
        .btn-check:checked + .btn-chip,
        .btn-check:active + .btn-chip,
        .btn-chip.active {
        background-color: #eef2f7;   /* claro */
        color: #111827;              /* escuro */
        border-color: #eef2f7;
        }

        /* Hover no marcado */
        .btn-check:checked + .btn-chip:hover {
        background-color: #e6ebf2;
        border-color: #e6ebf2;
        }

        /* Desabilitado (se precisar) */
        .btn-check:disabled + .btn-chip {
        opacity: .6;
        pointer-events: none;
        }

    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="text-light h4 m-0">Saldo</h1>
        <a href="{{ url('/') }}" class="btn btn-outline-light">Voltar ao Painel</a>
    </div>

    <div class="card p-3 mb-3">
        <form method="get" action="{{ route('economies.saldo') }}" class="row gy-3 align-items-end">
            @php
                $mode = $mode ?? request('mode', 'single');
                $selectedMonth  = $selectedMonth ?? request('month');
                $selectedMonths = $selectedMonths ?? (array) request('months', []);
                // Agrupa meses por ano p/ UI
                $months = $months ?? [];
                $grouped = collect($months)->groupBy(function($m){ return substr($m['ym'], 0, 4); })->sortKeysDesc();
                // Índice para marcar ordem (mais recente primeiro)
                $__order = 0;
            @endphp

            <div class="col-12">
                <label class="form-label">Como deseja calcular?</label>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mode" id="modeSingle" value="single" {{ $mode==='single' ? 'checked' : '' }}>
                        <label class="form-check-label" for="modeSingle">Por mês</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mode" id="modeMulti" value="multi" {{ $mode==='multi' ? 'checked' : '' }}>
                        <label class="form-check-label" for="modeMulti">Selecionar meses específicos (somados)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mode" id="modeAll" value="all" {{ $mode==='all' ? 'checked' : '' }}>
                        <label class="form-check-label" for="modeAll">Tudo que já cadastrei</label>
                    </div>
                </div>
            </div>

            <!-- MODO: UM MÊS -->
            <div class="col-md-4 mode-single">
                <label class="form-label">Mês</label>
                <select name="month" class="form-select">
                    @foreach ($months as $m)
                        <option value="{{ $m['ym'] }}" {{ ($selectedMonth ?? '') === $m['ym'] ? 'selected' : '' }}>
                            {{ $m['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- MODO: VÁRIOS MESES (CHIPS) -->
            <div class="col-12 mode-multi">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="text-muted-invert small">Atalhos:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pick-current">Mês atual</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-recent="3">Últimos 3</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-recent="6">Últimos 6</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pick-all">Selecionar tudo</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="clear-all">Limpar</button>
                </div>

                @forelse ($grouped as $year => $rows)
                    <div class="mb-2">
                        <div class="year-badge mb-1">{{ $year }}</div>
                        @foreach ($rows as $m)
                            @php
                                $isChecked = in_array($m['ym'], (array) $selectedMonths);
                                $id = 'm_' . str_replace('-', '_', $m['ym']);
                            @endphp
                            <input
                                type="checkbox"
                                class="btn-check month-check"
                                id="{{ $id }}"
                                name="months[]"
                                value="{{ $m['ym'] }}"
                                data-year="{{ $year }}"
                                data-order="{{ $__order }}"
                                {{ $isChecked ? 'checked' : '' }}
                                autocomplete="off"
                            >
                            <label class="btn btn btn-sm btn-chip" for="{{ $id }}">
                                {{ $m['label'] }}
                            </label>
                            @php $__order++; @endphp
                        @endforeach
                    </div>
                @empty
                    <div class="text-muted">Não há meses cadastrados ainda.</div>
                @endforelse
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Calcular</button>
                <a href="{{ route('economies.saldo') }}" class="btn btn-outline-dark">Limpar</a>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card p-3">
                <h6 class="mb-2">Totais</h6>
                <div>Rendas: <span class="stat text-success">R$ {{ number_format($totalRendas ?? 0, 2, ',', '.') }}</span></div>
                <div>Gastos: <span class="stat text-danger">R$ {{ number_format($totalGastos ?? 0, 2, ',', '.') }}</span></div>
                <div>Saldo:
                    @php $saldoVal = $saldo ?? 0; @endphp
                    <span class="stat {{ $saldoVal >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($saldoVal, 2, ',', '.') }}
                    </span>
                </div>
                <div class="small text-muted-invert mt-2">
                    @if(($breakdown ?? []) && count($breakdown) > 1)
                        Soma de {{ count($breakdown) }} meses selecionados.
                    @elseif(($breakdown ?? []) && count($breakdown) === 1)
                        Mês selecionado: {{ $breakdown[0]['label'] }}
                    @else
                        Sem dados para o(s) mês(es) selecionado(s).
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card p-3">
                <h6 class="mb-2">Resumo por mês</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th class="text-end">Rendas</th>
                                <th class="text-end">Gastos</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($breakdown ?? []) as $m)
                                <tr>
                                    <td>{{ $m['label'] }}</td>
                                    <td class="text-end">R$ {{ number_format($m['rendas'], 2, ',', '.') }}</td>
                                    <td class="text-end">R$ {{ number_format($m['gastos'], 2, ',', '.') }}</td>
                                    <td class="text-end {{ $m['saldo']>=0 ? 'text-success' : 'text-danger' }}">
                                        R$ {{ number_format($m['saldo'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function(){
    const modeRadios = document.querySelectorAll('input[name="mode"]');
    const singleEls = document.querySelectorAll('.mode-single');
    const multiEls  = document.querySelectorAll('.mode-multi');
    const checks    = () => Array.from(document.querySelectorAll('.month-check'));

    function updateVisibility() {
        const mode = document.querySelector('input[name="mode"]:checked')?.value || 'single';
        singleEls.forEach(el => el.style.display = (mode === 'single') ? '' : 'none');
        multiEls.forEach(el => el.style.display  = (mode === 'multi')  ? '' : 'none');
    }
    modeRadios.forEach(r => r.addEventListener('change', updateVisibility));
    updateVisibility();

    // Atalhos
    document.querySelector('[data-recent="3"]')?.addEventListener('click', () => selectRecent(3));
    document.querySelector('[data-recent="6"]')?.addEventListener('click', () => selectRecent(6));
    document.getElementById('pick-all')?.addEventListener('click', () => setAll(true));
    document.getElementById('clear-all')?.addEventListener('click', () => setAll(false));
    document.getElementById('pick-current')?.addEventListener('click', () => selectRecent(1));

    function setAll(flag){
        checks().forEach(c => c.checked = !!flag);
    }
    function selectRecent(n){
        const list = checks().sort((a,b) => (+a.dataset.order) - (+b.dataset.order)); // 0 = mais recente
        list.forEach((c,i) => c.checked = i < n);
    }
})();
</script>
</body>
</html>
