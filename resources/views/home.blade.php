<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{asset('assets/bootstrap/bootstrap.min.css')}}">
    
    <style>
        body {
            background-color: #30373F; /* Cor de fundo clara do Bootstrap */
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
        <main class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <h1 class="card-title">Painel</h1>
                <p class="text-muted">Opções de Gerenciamento</p>
            </div>

            <div class="d-grid gap-2">
                <a href="send_values" class="btn btn-outline-secondary" role="button">
                    Enviar Economias
                </a>
                <a href="pagina2.php" class="btn btn-outline-secondary" role="button">
                    Página 2
                </a>
                <a href="pagina3.php" class="btn btn-outline-secondary" role="button">
                    Página 3
                </a>
                <a href="pagina4.php" class="btn btn-outline-secondary" role="button">
                    Página 4
                </a>
                <a href="pagina5.php" class="btn btn-outline-secondary" role="button">
                    Página 5
                </a>
            </div>
        </main>
    </div>
</body>
</html>