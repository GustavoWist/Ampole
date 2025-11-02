<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe seus gastos</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-10">
            <div class="card p-5">
                <div class="row justify-content-center">
                    <h1 class="text-center mb-4">Informe seus gastos</h1>
                    <div class="col-12">

                        <form action="{{ route('gastos.store') }}" method="post" novalidate>
                            @csrf

                            <h4 class="mb-3 d-flex justify-content-between align-items-center">
                                Gastos
                                <button type="button" class="btn btn-primary btn-sm" id="add-gasto-btn">+</button>
                            </h4>

                            <div id="gastos-container">
                                <!-- Primeira linha como exemplo inicial -->
                                <div class="row mb-3 gasto-item" data-index="0">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Descrição</label>
                                        <input type="text" class="form-control" name="gastos[0][descricao]" placeholder="Ex: Aluguel, Mercado" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Valor</label>
                                        <input type="number" step="0.01" class="form-control text-danger" name="gastos[0][valor]" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Data (opcional)</label>
                                        <input type="date" class="form-control" name="gastos[0][data]">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 mt-4">
                                <button type="submit" class="btn btn-secondary w-100">Salvar gastos</button>
                            </div>
                        </form>

                        @if(session('loginError'))
                            <div class="alert alert-danger text-center mt-3">
                                {{ session('loginError') }}
                            </div>
                        @endif

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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('gastos-container');
    const addButton = document.getElementById('add-gasto-btn');
    let index = container.querySelectorAll('.gasto-item').length - 1;

    const getRow = (i) => `
        <div class="row mb-3 gasto-item" data-index="${i}">
            <div class="col-md-6 mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" class="form-control" name="gastos[${i}][descricao]" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Valor</label>
                <input type="number" step="0.01" class="form-control text-danger" name="gastos[${i}][valor]" required>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Data</label>
                <input type="date" class="form-control" name="gastos[${i}][data]">
            </div>
            <div class="col-md-1 d-flex align-items-end mb-3">
                <button type="button" class="btn btn-danger w-100 remove-gasto-btn">&times;</button>
            </div>
        </div>
    `;

    addButton.addEventListener('click', () => {
        index++;
        container.insertAdjacentHTML('beforeend', getRow(index));
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-gasto-btn')) {
            e.target.closest('.gasto-item').remove();
        }
    });
});
</script>
</body>
</html>
