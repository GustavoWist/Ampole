<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análises & Gráficos</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #30373F; }
        .card { border-radius: 14px; }
        .stat { font-size: 1.4rem; }
        .text-muted-invert { color: #cbd5e1; }
        .table-sm td, .table-sm th { padding: .4rem .5rem; }
    </style>
</head>
<body>


<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="text-light h3 m-0">Análises & Gráficos</h1>
        <a class="btn btn-outline-light" href="{{ route('analytics.index') }}">Atualizar</a>
    </div>

    <div class="card p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label">Período</label>
                <select id="months" class="form-select">
                    @php $mSel = (int) request('months', 12); @endphp
                    <option value="3"  {{ $mSel===3  ? 'selected' : '' }}>Últimos 3 meses</option>
                    <option value="6"  {{ $mSel===6  ? 'selected' : '' }}>Últimos 6 meses</option>
                    <option value="12" {{ $mSel===12 ? 'selected' : '' }}>Últimos 12 meses</option>
                    <option value="24" {{ $mSel===24 ? 'selected' : '' }}>Últimos 24 meses</option>
                    <option value="36" {{ $mSel===36 ? 'selected' : '' }}>Últimos 36 meses</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-9 text-end">
                <a href="{{ route('economies.saldo') }}" class="btn btn-primary">Ver Saldo</a>
                <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Rendas</a>
                <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Gastos</a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card p-3" style="min-height: 420px;">
                <h5 class="mb-3">Rendas × Gastos (mensal) + Saldo</h5>
                <div style="height: 360px;">
                    <canvas id="chartCashflow"></canvas>
                </div>
                <div id="chartNotice" class="small text-muted-invert mt-2" style="display:none;"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6 class="mb-2">Totais do período</h6>
                <div class="mt-2">
                    <div>Rendas: <span id="totR" class="stat text-success">—</span></div>
                    <div>Gastos: <span id="totG" class="stat text-danger">—</span></div>
                    <div>Saldo:  <span id="totS" class="stat">—</span></div>
                    <div>Saving rate: <span id="totSR" class="stat">—</span></div>
                </div>
            </div>
            <div class="card p-3">
                <h6 class="mb-2">Insights rápidos</h6>
                <ul id="insights" class="mb-0">
                    <li>Carregando…</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Resumo por mês -->
    <div class="card p-3 mt-3">
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
                <tbody id="monthRows">
                    <tr><td colspan="4" class="text-muted">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="{{ route('analytics.index') }}" class="btn btn-outline-light">Voltar ao topo</a>
        <a href="{{ url('/') }}" class="btn btn-outline-light">Home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let chart;
const elMonths   = document.getElementById('months');
const elTotR     = document.getElementById('totR');
const elTotG     = document.getElementById('totG');
const elTotS     = document.getElementById('totS');
const elTotSR    = document.getElementById('totSR');
const elInsights = document.getElementById('insights');
const elNotice   = document.getElementById('chartNotice');
const elMonthRows= document.getElementById('monthRows');

function brl(n) {
    return (n ?? 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
function media(arr){ if(!arr || !arr.length) return 0; return arr.reduce((a,b)=>a+b,0)/arr.length; }

function renderTable(breakdown){
    if(!breakdown || !breakdown.length){
        elMonthRows.innerHTML = `<tr><td colspan="4" class="text-muted">Sem dados para o período.</td></tr>`;
        return;
    }
    elMonthRows.innerHTML = breakdown.map(m =>
        `<tr>
            <td>${m.label}</td>
            <td class="text-end">${brl(m.rendas)}</td>
            <td class="text-end">${brl(m.gastos)}</td>
            <td class="text-end ${m.saldo>=0?'text-success':'text-danger'}">${brl(m.saldo)}</td>
        </tr>`
    ).join('');
}

function render(data) {
    // Totais
    const totals = data.totals || { rendas:0, gastos:0, saldo:0, saving_rate:0 };
    elTotR.textContent = brl(totals.rendas);
    elTotG.textContent = brl(totals.gastos);
    elTotS.textContent = brl(totals.saldo);
    elTotS.className = 'stat ' + ((totals.saldo ?? 0) >= 0 ? 'text-success' : 'text-danger');
    elTotSR.textContent = `${totals.saving_rate}%`;

    // Tabela mês a mês
    renderTable(data.months_breakdown || []);

    // Insights (melhor/pior mês, baseados no saldo mensal)
    const labels = data.labels || [];
    const ds     = data.datasets || { rendas:[], gastos:[], saldo:[] };
    if (ds.saldo && ds.saldo.length) {
        const maxSaldo = Math.max(...ds.saldo);
        const minSaldo = Math.min(...ds.saldo);
        const iMax = ds.saldo.indexOf(maxSaldo);
        const iMin = ds.saldo.indexOf(minSaldo);
        elInsights.innerHTML = `
            <li>Melhor mês (saldo): <strong>${labels[iMax]}</strong> (${brl(maxSaldo)})</li>
            <li>Pior mês (saldo): <strong>${labels[iMin]}</strong> (${brl(minSaldo)})</li>
            <li>Média mensal de rendas: <strong>${brl(media(ds.rendas||[]))}</strong></li>
            <li>Média mensal de gastos: <strong>${brl(media(ds.gastos||[]))}</strong></li>
        `;
    } else {
        elInsights.innerHTML = `<li>Sem dados para o período selecionado.</li>`;
    }

    // Gráfico
    const ctx = document.getElementById('chartCashflow').getContext('2d');
    if (chart) chart.destroy();

    if (!window.Chart) {
        elNotice.style.display = 'block';
        elNotice.textContent = 'Não foi possível carregar o Chart.js.';
        return;
    }

    elNotice.style.display = 'none';
    chart = new Chart(ctx, {
        data: {
            labels: labels,
            datasets: [
                { type: 'bar',  label: 'Rendas', data: ds.rendas },
                { type: 'bar',  label: 'Gastos', data: ds.gastos },
                { type: 'line', label: 'Saldo',  data: ds.saldo, tension: 0.2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { callback: (v)=> brl(v) } }
            },
            plugins: {
                tooltip: {
                    callbacks: { label: (ctx)=> `${ctx.dataset.label}: ${brl(ctx.parsed.y)}` }
                }
            }
        }
    });
}

async function loadData() {
    try {
        const months = elMonths.value;
        const url = `{{ route('analytics.data') }}?months=${months}`;
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        render(data);
    } catch (err) {
        console.error('Falha ao carregar dados do analytics:', err);
        elNotice.style.display = 'block';
        elNotice.textContent = `Não foi possível carregar os dados (${err.message}).`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Render inicial com payload do servidor (já mensal)
    render(window.__AN || {
        labels:[], datasets:{rendas:[],gastos:[],saldo:[]},
        totals:{rendas:0,gastos:0,saldo:0,saving_rate:0},
        months_breakdown:[]
    });
    // Buscar dados ao trocar o período
    elMonths.addEventListener('change', loadData);
});
</script>
</body>
</html>
