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

                            <form action="{{ route('economies.store')}}" method="post" novalidate>
                                @csrf

                                <h4 class="mb-3">Renda Principal</h4>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="renda_principal_origem" class="form-label">Origem Principal</label>
                                        <input type="text" class="form-control" name="rendas[0][origem]" id="renda_principal_origem" value="{{ old('rendas.0.origem', 'Salário Principal') }}" required>
                                        </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="renda_principal_valor" class="form-label">Valor Principal</label>
                                        <input type="number" step="0.01" class="form-control text-info" name="rendas[0][valor]" id="renda_principal_valor" value="{{ old('rendas.0.valor') }}" required>
                                    </div>
                                </div>
                                <hr>

                                <h4 class="mt-4 mb-3 d-flex justify-content-between align-items-center">
                                    Rendas Extras
                                    <button type="button" class="btn btn-primary btn-sm" id="add-renda-btn">+</button>
                                </h4>
                                
                                <div id="rendas-extras-container">
                                    <div class="row mb-3 renda-extra-item" data-index="1">
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Origem Extra</label>
                                            <input type="text" class="form-control" name="rendas[1][origem]" value="{{ old('rendas.1.origem') }}" placeholder="Ex: Freela, Aluguel, etc.">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Valor Extra</label>
                                            <input type="number" step="0.01" class="form-control text-info" name="rendas[1][valor]" value="{{ old('rendas.1.valor') }}" required>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end mb-3">
                                            <button type="button" class="btn btn-danger w-100 remove-renda-btn">Remover</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 mt-4">
                                    <button type="submit" class="btn btn-secondary w-100">Finalizar</button>
                                </div>
                            </form>

                            {{-- Tratamento de Erros (Se houver) --}}
                            @if(session('loginError'))
                                <div class="alert alert-danger text-center mt-3">
                                    {{ session('loginError') }}
                                </div>
                            @endif
                            
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul>
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
        // Bloco de JavaScript para adicionar/remover dinamicamente
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('rendas-extras-container');
            const addButton = document.getElementById('add-renda-btn');
            
            // Pega o índice máximo atual (o 0 é a principal)
            let index = Math.max(1, container.querySelectorAll('.renda-extra-item').length);

            // Template do campo extra (usamos data-index="INDEX_PLACEHOLDER" para facilitar)
            const getNewRendaTemplate = (newIndex) => {
                return `
                    <div class="row mb-3 renda-extra-item" data-index="${newIndex}">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Origem Extra ${newIndex}</label>
                            <input type="text" class="form-control" name="rendas[${newIndex}][origem]" placeholder="Ex: Freela, Aluguel, etc.">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Valor Extra ${newIndex}</label>
                            <input type="number" step="0.01" class="form-control text-info" name="rendas[${newIndex}][valor]" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button type="button" class="btn btn-danger w-100 remove-renda-btn">Remover</button>
                        </div>
                    </div>
                `;
            };

            // Função para adicionar um novo campo
            const addRenda = () => {
                index++;
                container.insertAdjacentHTML('beforeend', getNewRendaTemplate(index));
            };

            // Adiciona o listener ao botão principal de adicionar
            addButton.addEventListener('click', addRenda);

            // Função para remover um campo e reindexar os nomes
            const removeRenda = (event) => {
                if (event.target.classList.contains('remove-renda-btn')) {
                    const itemToRemove = event.target.closest('.renda-extra-item');
                    itemToRemove.remove();
                    
                    // Reindexar (opcional, mas bom para manter a ordem limpa)
                    reindexRendas();
                }
            };
            
            // Listener para remover (usa delegação de evento)
            container.addEventListener('click', removeRenda);
            
            // Função para reindexar todos os campos após uma remoção
            const reindexRendas = () => {
                const items = container.querySelectorAll('.renda-extra-item');
                let newIndex = 1; // Começa em 1, pois a principal é 0
                items.forEach(item => {
                    // Atualiza os nomes dos inputs e o data-index
                    item.dataset.index = newIndex;
                    item.querySelector(`input[name^="rendas["][name$="[origem]"]`).name = `rendas[${newIndex}][origem]`;
                    item.querySelector(`input[name^="rendas["][name$="[valor]"]`).name = `rendas[${newIndex}][valor]`;
                    item.querySelector('label').textContent = `Origem Extra ${newIndex}`;
                    item.querySelector('.col-md-5:nth-child(2) label').textContent = `Valor Extra ${newIndex}`;
                    newIndex++;
                });
                // Atualiza a variável de controle para o próximo "add"
                index = Math.max(1, newIndex - 1);
            }
            
        });
    </script>
</body>
</html>