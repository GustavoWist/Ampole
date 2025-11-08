<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Projeções Futuras</title>
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
  <style>
    body { background: #30373F; }
    .card { border-radius: 14px; }
    .stat { font-size: 1.4rem; }
    .text-muted-invert { color: #cbd5e1; }
    .table-sm td, .table-sm th { padding: .4rem .5rem; }
    .legend-pill { padding: .2rem .5rem; border-radius: 9999px; font-size: .8rem; background:#eef2f7; color:#111827; }
  </style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="text-light h3 m-0">Projeções Futuras</h1>
    <a class="btn btn-outline-light" href="{{ route('projecoes.index') }}">Atualizar</a>
  </div>

  <!-- Filtro -->
  <div class="card p-3 mb-3">
    <div class="row g-3 align-items-end">
      <div class="col-sm-6 col-md-3">
        <label class="form-label">Período futuro</label>
        <select id="monthsAhead" class="form-select">
          <option value="3"  selected>Próximos 3 meses</option>
          <option value="6">Próximos 6 meses</option>
          <option value="12">Próximos 12 meses</option>
          <option value="24">Próximos 24 meses</option>
        </select>
      </div>
      <div class="col-sm-6 col-md-9 text-end">
        <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">Analytics (histórico)</a>
        <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Rendas</a>
        <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Gastos</a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card p-3" style="min-height: 420px;">
        <h5 class="mb-2">Rendas × Gastos (planejado) + Saldos</h5>
        <div class="mb-2 d-flex flex-wrap gap-2">
          <span class="legend-pill">Barras: Rendas & Gastos</span>
          <span class="legend-pill">Linha: Saldo do mês</span>
          <span class="legend-pill">Linha: Saldo acumulado</span>
        </div>
        <div style="height: 360px;">
          <canvas id="chartFuture"></canvas>
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
        <h6 class="mb-2">Insights planejados</h6>
        <ul id="insights" class="mb-0">
          <li>Carregando…</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Resumo por mês -->
  <div class="card p-3 mt-3">
    <h6 class="mb-2">Resumo por mês (planejado)</h6>
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
    <a href="{{ route('projecoes.index') }}" class="btn btn-outline-light">Voltar ao topo</a>
    <a href="{{ url('/') }}" class="btn btn-outline-light">Home</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let chart;
const elMonths    = document.getElementById('monthsAhead');
const elTotR      = document.getElementById('totR');
const elTotG      = document.getElementById('totG');
const elTotS      = document.getElementById('totS');
const elTotSR     = document.getElementById('totSR');
const elInsights  = document.getElementById('insights');
const elNotice    = document.getElementById('chartNotice');
const elMonthRows = document.getElementById('monthRows');

function brl(n){ return (n ?? 0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }
function media(arr){ if(!arr || !arr.length) return 0; return arr.reduce((a,b)=>a+b,0)/arr.length; }
function sum(arr){ return (arr||[]).reduce((a,b)=>a+(+b||0),0); }

function normalizePayload(data){
  const labels = data.labels || [];
  const rendas = data.rendas || [];
  const gastos = data.gastos || [];
  const saldoMes  = data.saldo_mes || [];
  const saldoAcum = data.saldo_acumulado || [];
  let totals = data.totals;
  if (!totals){
    const tR = sum(rendas), tG = sum(gastos), tS = tR - tG;
    totals = { rendas:tR, gastos:tG, saldo:tS, saving_rate: (tR>0?((tS/tR)*100):0).toFixed(1) };
  }
  let months_breakdown = data.months_breakdown || labels.map((label, i) => ({
    label, rendas:+(rendas[i]||0), gastos:+(gastos[i]||0), saldo:+(saldoMes[i]||0)
  }));
  return { labels, rendas, gastos, saldoMes, saldoAcum, totals, months_breakdown };
}

function renderTable(breakdown){
  if(!breakdown || !breakdown.length){
      elMonthRows.innerHTML = `<tr><td colspan="4" class="text-muted">Sem dados planejados para o período.</td></tr>`;
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

function render(raw){
  const data = normalizePayload(raw);

  // Totais
  elTotR.textContent = brl(data.totals.rendas);
  elTotG.textContent = brl(data.totals.gastos);
  elTotS.textContent = brl(data.totals.saldo);
  elTotS.className = 'stat ' + ((data.totals.saldo ?? 0) >= 0 ? 'text-success' : 'text-danger');
  elTotSR.textContent = `${data.totals.saving_rate}%`;

  // Tabela
  renderTable(data.months_breakdown);

  // Insights
  if (data.saldoMes && data.saldoMes.length) {
    const maxSaldo = Math.max(...data.saldoMes);
    const minSaldo = Math.min(...data.saldoMes);
    const iMax = data.saldoMes.indexOf(maxSaldo);
    const iMin = data.saldoMes.indexOf(minSaldo);
    elInsights.innerHTML = `
      <li>Melhor mês (saldo planejado): <strong>${data.labels[iMax] ?? '-'}</strong> (${brl(maxSaldo)})</li>
      <li>Pior mês (saldo planejado): <strong>${data.labels[iMin] ?? '-'}</strong> (${brl(minSaldo)})</li>
      <li>Média mensal de rendas (plan.): <strong>${brl(media(data.rendas||[]))}</strong></li>
      <li>Média mensal de gastos (plan.): <strong>${brl(media(data.gastos||[]))}</strong></li>
    `;
  } else {
    elInsights.innerHTML = `<li>Sem dados planejados para o período selecionado.</li>`;
  }

  // Gráfico
  const ctx = document.getElementById('chartFuture').getContext('2d');
  if (chart) chart.destroy();

  if (!window.Chart) {
    elNotice.style.display = 'block';
    elNotice.textContent = 'Não foi possível carregar o Chart.js.';
    return;
  }

  elNotice.style.display = 'none';
  chart = new Chart(ctx, {
    data: {
      labels: data.labels,
      datasets: [
        { type: 'bar',  label: 'Rendas (plan.)', data: data.rendas },
        { type: 'bar',  label: 'Gastos (plan.)', data: data.gastos },
        { type: 'line', label: 'Saldo do mês (plan.)', data: data.saldoMes, tension: 0.25, yAxisID: 'y' },
        { type: 'line', label: 'Saldo acumulado (plan.)', data: data.saldoAcum, tension: 0.25, yAxisID: 'y' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      // tooltip só do ponto/coluna sob o mouse
      interaction: { mode: 'nearest', intersect: true },
      scales: {
        x: { ticks: { color: '#e5e7eb' }, grid: { color: 'rgba(229,231,235,.12)' } },
        y: {
          beginAtZero: true,
          ticks: { color: '#e5e7eb', callback: v => brl(v) },
          grid: { color: 'rgba(229,231,235,.12)' }
        }
      },
      plugins: {
        legend: { labels: { color: '#e5e7eb' } },
        tooltip: { callbacks: { label: (ctx)=> `${ctx.dataset.label}: ${brl(ctx.parsed.y)}` } }
      }
    }
  });
}

async function loadData(){
  try{
    const months = elMonths.value || '3';
    const url = `{{ route('projecoes.data') }}?months_ahead=${months}`;
    const res = await fetch(url, { credentials: 'same-origin' });
    if(!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    render(data);
  }catch(err){
    console.error('Falha ao carregar projeções:', err);
    elNotice.style.display = 'block';
    elNotice.textContent = `Não foi possível carregar os dados (${err.message}).`;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  elMonths.value = '3'; // default
  loadData();
  elMonths.addEventListener('change', loadData);
});
</script>
</body>
</html>
