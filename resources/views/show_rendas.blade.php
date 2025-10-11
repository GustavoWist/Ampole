<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Rendas</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-sm-10">
                <div class="card p-5">
                    <div class="text-center mb-4">
                        <h1 class="mb-3">Minhas Rendas</h1>
                        <p class="text-muted">Confira abaixo as rendas cadastradas</p>
                    </div>

                    @if ($rendas->isEmpty())
                        <div class="alert alert-info text-center">
                            Você ainda não possui rendas cadastradas.
                        </div>
                    @else
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Origem</th>
                                    <th>Valor (R$)</th>
                                    <th>Data de Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($rendas as $renda)
                                    <tr>
                                        <td>{{ ucfirst(strtolower($renda->origem)) }}</td>
                                        <td class="text-success fw-bold">{{ number_format($renda->valor, 2, ',', '.') }}</td>
                                        <td>{{ $renda->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            <form action="{{ route('economies.destroy', $renda->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta renda?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    @endif

                    <div class="mt-4 text-center">
                        <a href="/ampole/public" class="btn btn-outline-secondary">Voltar ao Painel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
