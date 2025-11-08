<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe suas rendas</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-10">
            <div class="card p-5">
                <div class="row justify-content-center">
                    <h1 class="text-center mb-4">Informe suas economias</h1>
                    <div class="col-12">

                        <form action="{{ route('economies.store') }}" method="post" novalidate>
                            @csrf

                            {{-- Renda Principal --}}
                            <h4 class="mb-3">Renda Principal</h4>
                            <div class="row mb-4">
                                <input type="hidden" name="rendas[0][id]" value="{{ $rendas[0]->id ?? '' }}">

                                <div class="col-md-4 mb-3">
                                    <label for="renda_principal_origem" class="form-label">Origem Principal</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="rendas[0][origem]"
                                        id="renda_principal_origem"
                                        value="{{ old('rendas.0.origem', $rendas[0]->origem ?? 'Salário Principal') }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="renda_principal_valor" class="form-label">Valor Principal</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control text-info"
                                        name="rendas[0][valor]"
                                        id="renda_principal_valor"
                                        value="{{ old('rendas.0.valor', $rendas[0]->valor ?? '') }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="renda_principal_data" class="form-label">Data</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        name="rendas[0][data]"
                                        id="renda_principal_data"
                                        value="{{ old('rendas.0.data',
                                            isset($rendas[0])
                                                ? ($rendas[0]->data ? $rendas[0]->data->format('Y-m-d') : ($rendas[0]->created_at ? $rendas[0]->created_at->format('Y-m-d') : ''))
                                                : ''
                                        ) }}"
                                    >
                                </div>
                            </div>
                            <hr>

                            {{-- Rendas Extras --}}
                            <h4 class="mt-4 mb-3 d-flex justify-content-between align-items-center">
                                Rendas Extras
                                <button type="button" class="btn btn-primary btn-sm" id="add-renda-btn">+</button>
                            </h4>

                            <div id="rendas-extras-container">
                                @php
                                    $extras = ($rendas->skip(1) ?? collect())->values();
                                @endphp

                                @foreach ($extras as $i => $extra)
                                    @php $idx = $i + 1; @endphp
                                    <div class="row mb-3 renda-extra-item" data-index="{{ $idx }}">
                                        <input type="hidden" name="rendas[{{ $idx }}][id]" value="{{ $extra->id }}">

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Origem Extra {{ $idx }}</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="rendas[{{ $idx }}][origem]"
                                                value="{{ old("rendas.$idx.origem", $extra->origem) }}"
                                            >
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Valor Extra {{ $idx }}</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="form-control text-info"
                                                name="rendas[{{ $idx }}][valor]"
                                                value="{{ old("rendas.$idx.valor", $extra->valor) }}"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Data Extra {{ $idx }}</label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                name="rendas[{{ $idx }}][data]"
                                                value="{{ old("rendas.$idx.data",
                                                    $extra->data ? $extra->data->format('Y-m-d') : ($extra->created_at ? $extra->created_at->format('Y-m-d') : '')
                                                ) }}"
                                            >
                                        </div>

                                        <div class="col-md-2 d-flex align-items-end mb-3">
                                            <button type="button" class="btn btn-danger w-100 remove-renda-btn">Remover</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3 mt-4">
                                <button type="submit" class="btn btn-secondary w-100">
                                    {{ $rendas->isEmpty() ? 'Salvar' : 'Atualizar' }}
                                </button>
                            </div>
                        </form>

                        <a href="{{ url('/') }}" class="btn btn-outline-secondary mt-3">Voltar ao Painel</a>

                        {{-- Erros --}}
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
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('rendas-extras-container');
    const addButton = document.getElementById('add-renda-btn');

    // Maior data-index atual
    const items = container.querySelectorAll('.renda-extra-item');
    let index = 0;
    if (items.length) {
        const last = items[items.length - 1];
        index = parseInt(last.dataset.index, 10) || items.length;
    }

    const getNewRendaTemplate = (newIndex) => {
        return `
            <div class="row mb-3 renda-extra-item" data-index="${newIndex}">
                <input type="hidden" name="rendas[${newIndex}][id]" value="">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Origem Extra ${newIndex}</label>
                    <input type="text" class="form-control" name="rendas[${newIndex}][origem]" placeholder="Ex: Freela, Aluguel, etc.">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor Extra ${newIndex}</label>
                    <input type="number" step="0.01" class="form-control text-info" name="rendas[${newIndex}][valor]" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data Extra ${newIndex}</label>
                    <input type="date" class="form-control" name="rendas[${newIndex}][data]">
                </div>
                <div class="col-md-2 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-danger w-100 remove-renda-btn">Remover</button>
                </div>
            </div>
        `;
    };

    const addRenda = () => {
        index += 1;
        container.insertAdjacentHTML('beforeend', getNewRendaTemplate(index));
    };

    addButton.addEventListener('click', addRenda);

    const removeRenda = (event) => {
        if (event.target.classList.contains('remove-renda-btn')) {
            const itemToRemove = event.target.closest('.renda-extra-item');
            itemToRemove.remove();
            reindexRendas();
        }
    };
    container.addEventListener('click', removeRenda);

    const reindexRendas = () => {
        const items = container.querySelectorAll('.renda-extra-item');
        let newIndex = 1; // 0 = principal

        items.forEach(item => {
            item.dataset.index = newIndex;

            const hiddenId = item.querySelector('input[type="hidden"][name^="rendas["][name$="[id]"]');
            if (hiddenId) hiddenId.name = `rendas[${newIndex}][id]`;

            const origem = item.querySelector('input[name^="rendas["][name$="[origem]"]');
            if (origem) origem.name = `rendas[${newIndex}][origem]`;

            const valor = item.querySelector('input[name^="rendas["][name$="[valor]"]');
            if (valor) valor.name = `rendas[${newIndex}][valor]`;

            const data = item.querySelector('input[name^="rendas["][name$="[data]"]');
            if (data) data.name = `rendas[${newIndex}][data]`;

            const labels = item.querySelectorAll('.form-label');
            if (labels[0]) labels[0].textContent = `Origem Extra ${newIndex}`;
            if (labels[1]) labels[1].textContent = `Valor Extra ${newIndex}`;
            if (labels[2]) labels[2].textContent = `Data Extra ${newIndex}`;

            newIndex++;
        });

        index = Math.max(0, newIndex - 1);
    };
});
</script>
</body>
</html>
