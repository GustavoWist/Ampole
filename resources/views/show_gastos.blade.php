<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus gastos</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Meus gastos</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($gastos->isEmpty())
        <div class="alert alert-info">Você ainda não possui gastos cadastrados.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th style="width: 160px;">Valor</th>
                        <th style="width: 140px;">Data</th>
                        <th style="width: 140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gastos as $g)
                        <tr>
                            <td>{{ ucfirst(mb_strtolower($g->descricao, 'UTF-8')) }}</td>
                            <td>R$ {{ number_format($g->valor, 2, ',', '.') }}</td>
                            <td>{{ $g->data ? \Carbon\Carbon::parse($g->data)->format('d-m-y') : '-' }}</td>
                            <td>
                                <form action="{{ route('gastos.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Excluir este gasto?')">
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
    @endif

    <a href="{{ route('gastos.create') }}" class="btn btn-primary mt-3">Adicionar gastos</a>
</div>
</body>
</html>
