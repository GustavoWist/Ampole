<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas rendas</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #30373F; }
        .card { border-radius: 14px; }
        .month-header {
            background: #1f2730;
            color: #eef2f7;
            font-weight: 600;
            letter-spacing: .3px;
        }
        .month-card { overflow: hidden; border: 1px solid #3a4654; }
        .table-sm td, .table-sm th { padding: .55rem .7rem; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0 text-light">Minhas rendas</h1>

        <form method="get" action="{{ route('economies.show') }}" class="row g-2 align-items-end">
            @php $sort = $sort ?? request('sort', 'date_desc'); @endphp
            <div class="col-auto">
                <label class="form-label mb-0 small text-light">Ordenar itens por</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="date_asc"   {{ $sort==='date_asc'   ? 'selected' : '' }}>Data (mais antigas)</option>
                    <option value="date_desc"  {{ $sort==='date_desc'  ? 'selected' : '' }}>Data (mais recentes)</option>
                    <option value="valor_asc"  {{ $sort==='valor_asc'  ? 'selected' : '' }}>Valor (menor → maior)</option>
                    <option value="valor_desc" {{ $sort==='valor_desc' ? 'selected' : '' }}>Valor (maior → menor)</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-light">Aplicar</button>
                <a href="{{ route('economies.show') }}" class="btn btn-sm btn-outline-dark">Limpar</a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($rendas->isEmpty())
        <div class="alert alert-info">Você ainda não possui rendas cadastradas.</div>
    @else
        @php
            // Agrupa por ano-mês usando data ou created_at
            $groups = $rendas
                ->groupBy(function($r) {
                    $dt = $r->data ?? $r->created_at;
                    return $dt ? \Carbon\Carbon::parse($dt)->format('Y-m') : 'sem-data';
                })
                ->sortKeys(); // meses em ordem crescente (YYYY-MM)

            // Rótulo bonito do mês
            $labelFor = function(string $ym) {
                if ($ym === 'sem-data') return 'Sem data';
                try {
                    return \Carbon\Carbon::createFromFormat('Y-m', $ym)
                        ->locale(app()->getLocale() ?: 'pt_BR')
                        ->translatedFormat('F Y');
                } catch (\Throwable $e) {
                    return \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('m/Y');
                }
            };

            // Ordena os itens dentro do mês conforme o seletor
            $sortItems = function($items, $sort) {
                switch ($sort) {
                    case 'valor_asc':
                        return $items->sortBy('valor');
                    case 'valor_desc':
                        return $items->sortByDesc('valor');
                    case 'date_asc':
                        return $items->sortBy(function($r){
                            $dt = $r->data ?? $r->created_at;
                            return $dt ? \Carbon\Carbon::parse($dt)->timestamp : PHP_INT_MAX;
                        });
                    case 'date_desc':
                    default:
                        return $items->sortByDesc(function($r){
                            $dt = $r->data ?? $r->created_at;
                            return $dt ? \Carbon\Carbon::parse($dt)->timestamp : -PHP_INT_MAX;
                        });
                }
            };
        @endphp

        @foreach ($groups as $ym => $itemsRaw)
            @php $items = $sortItems($itemsRaw, $sort); @endphp
            <div class="card month-card mb-4 shadow-sm">
                <div class="card-header month-header">
                    {{ ucfirst($labelFor($ym)) }}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Origem</th>
                                    <th style="width: 160px;">Valor</th>
                                    <th style="width: 140px;">Data</th>
                                    <th style="width: 220px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $r)
                                    <tr>
                                        <td>{{ ucfirst(mb_strtolower($r->origem, 'UTF-8')) }}</td>
                                        <td>R$ {{ number_format($r->valor, 2, ',', '.') }}</td>
                                        <td>
                                            @if($r->data)
                                                {{ $r->data->format('d-m-y') }}
                                            @else
                                                {{ $r->created_at?->format('d-m-y') ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="d-flex gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('economies.edit', $r->id) }}">Editar</a>
                                            <form action="{{ route('economies.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Excluir esta renda?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('economies.create') }}" class="btn btn-primary">Adicionar rendas</a>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary">Voltar ao Painel</a>
    </div>
</div>
</body>
</html>
