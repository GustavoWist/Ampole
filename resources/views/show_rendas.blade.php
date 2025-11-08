<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas rendas</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Minhas rendas</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($rendas->isEmpty())
        <div class="alert alert-info">Você ainda não possui rendas cadastradas.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Origem</th>
                        <th style="width: 160px;">Valor</th>
                        <th style="width: 140px;">Data</th>
                        <th style="width: 140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rendas as $r)
                        <tr>
                            <td>{{ ucfirst(mb_strtolower($r->origem, 'UTF-8')) }}</td>
                            <td>R$ {{ number_format($r->valor, 2, ',', '.') }}</td>
                            <td>
                                @if($r->data)
                                    {{ $r->data->format('d-m-y') }}
                                @else
                                    {{ $r->created_at?->format('d-m-y') }}
                                @endif
                            </td>
                            <td>
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
    @endif

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('economies.edit') }}" class="btn btn-primary">Adicionar/Editar rendas</a>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary">Voltar ao Painel</a>
    </div>
</div>
</body>
</html>
