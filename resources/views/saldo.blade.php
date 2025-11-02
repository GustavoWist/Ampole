<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Resumo Financeiro</h1>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Total de Rendas</h5>
                <div class="fs-4 text-success">R$ {{ number_format($totalRendas, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Total de Gastos</h5>
                <div class="fs-4 text-danger">R$ {{ number_format($totalGastos, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            @php $positivo = $saldo >= 0; @endphp
            <div class="card p-3">
                <h5>Saldo</h5>
                <div class="fs-3 {{ $positivo ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($saldo, 2, ',', '.') }}
                </div>
                <small class="text-muted">{{ $positivo ? 'Você está no positivo.' : 'Atenção: saldo negativo.' }}</small>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Ver rendas</a>
        <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Ver gastos</a>
        <a href="{{ route('gastos.create') }}" class="btn btn-primary">Adicionar gastos</a>
    </div>
</div>
</body>
</html>
