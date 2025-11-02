<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
    <style>
        body {
            background-color: #30373F;
        }
        .custom-card-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="custom-card-container">
    <main class="card p-4 shadow" style="width: 100%; max-width: 420px;">
        <div class="text-center mb-4">
            <h1 class="card-title">Painel</h1>
            <p class="text-muted">Opções de Gerenciamento</p>
        </div>

        <div class="d-grid gap-2">
            {{-- Rendas --}}
            <a href="{{ route('economies.edit') }}" class="btn btn-outline-secondary" role="button">
                Enviar Economias
            </a>
            <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary" role="button">
                Minhas Economias
            </a>

            {{-- Gastos --}}
            <a href="{{ route('gastos.create') }}" class="btn btn-outline-secondary" role="button">
                Adicionar Gastos
            </a>
            <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary" role="button">
                Meus Gastos
            </a>

            {{-- Saldo (Rendas - Gastos) --}}
            <a href="{{ route('economies.saldo') }}" class="btn btn-outline-secondary" role="button">
                Ver Saldo
            </a>

            {{-- Sair --}}
            <a href="{{ url('logout') }}" class="btn btn-outline-secondary" role="button">
                Sair
            </a>
        </div>
    </main>
</div>
</body>
</html>
