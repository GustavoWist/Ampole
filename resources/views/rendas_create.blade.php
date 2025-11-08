<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar rendas</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-10">
            <div class="card p-5">
                <h1 class="text-center mb-4">Adicionar rendas</h1>

                <form action="{{ route('economies.storeMany') }}" method="post" novalidate>
                    @csrf

                    <div id="rendas-container">
                        <!-- uma linha inicial -->
                        <div class="row g-3 renda-item" data-index="0">
                            <div class="col-md-5">
                                <label class="form-label">Origem</label>
                                <input type="text" name="rendas[0][origem]" class="form-control" placeholder="Ex: Salário, Freela..." required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Valor</label>
                                <input type="number" step="0.01" name="rendas[0][valor]" class="form-control text-info" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="rendas[0][data]" class="form-control">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rendas[0][principal]" value="1" id="p0">
                                    <label class="form-check-label small" for="p0">Princ.</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">+ Adicionar linha</button>
                        <button type="submit" class="btn btn-secondary w-100">Salvar</button>
                    </div>
                </form>

                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Minhas rendas</a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Voltar ao Painel</a>
                </div>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('rendas-container');
    const addBtn = document.getElementById('add-row');
    let index = 0;

    const tpl = (i) => `
        <div class="row g-3 renda-item mt-2" data-index="${i}">
            <div class="col-md-5">
                <label class="form-label">Origem</label>
                <input type="text" name="rendas[${i}][origem]" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor</label>
                <input type="number" step="0.01" name="rendas[${i}][valor]" class="form-control text-info" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data</label>
                <input type="date" name="rendas[${i}][data]" class="form-control">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="rendas[${i}][principal]" value="1" id="p${i}">
                    <label class="form-check-label small" for="p${i}">Princ.</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remover</button>
            </div>
        </div>
    `;

    addBtn.addEventListener('click', () => {
        index += 1;
        container.insertAdjacentHTML('beforeend', tpl(index));
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.renda-item').remove();
        }
    });
});
</script>
</body>
</html>
