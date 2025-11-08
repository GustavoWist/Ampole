<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar renda</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-sm-10">
            <div class="card p-5">
                <h1 class="mb-4">Editar renda</h1>

                <form action="{{ route('economies.update', $renda->id) }}" method="post" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Origem</label>
                            <input type="text" name="origem" class="form-control" value="{{ old('origem', $renda->origem) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valor</label>
                            <input type="number" step="0.01" name="valor" class="form-control text-info" value="{{ old('valor', $renda->valor) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data</label>
                            <input type="date" name="data" class="form-control" value="{{ old('data', $renda->data ? $renda->data->format('Y-m-d') : ($renda->created_at?->format('Y-m-d'))) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="principal" value="1" id="principal" {{ old('principal', $renda->is_principal) ? 'checked' : '' }}>
                                <label class="form-check-label" for="principal">Renda principal</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-secondary">Salvar alterações</button>
                        <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Voltar</a>
                    </div>
                </form>

                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
</body>
</html>
