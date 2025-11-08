<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
  <style>
    body { background-color: #30373F; }
    .custom-card-container {
      min-height: 100vh; display:flex; justify-content:center; align-items:center;
    }
    .topbar {
      position: fixed; top: 0; left: 0; right: 0;
      padding: 10px 16px; display:flex; justify-content:flex-end; align-items:center;
      background: transparent; z-index: 1000;
    }
    .user-chip {
      display: inline-flex; align-items: center; gap: 10px;
      background: rgba(0,0,0,0.25); color: #fff;
      padding: 6px 10px; border-radius: 9999px; text-decoration: none;
    }
    .user-chip:hover { background: rgba(0,0,0,0.35); }
    .avatar {
      width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background:#444;
      border: 2px solid rgba(255,255,255,0.2);
    }
  </style>
</head>
<body>

  @php
    // tenta usar o $user vindo do controller; se não vier, busca pelo id da sessão
    $current = $user ?? null;
    if (!$current && session('user.id')) {
        $current = \App\Models\User::find(session('user.id'));
    }

    $avatarPath = $current?->avatar; // ex: "avatars/xxxx.jpg"
    $avatarUrl  = $avatarPath ? asset('storage/'.ltrim($avatarPath, '/')) : asset('assets/img/avatar-default.png');
    $username   = $current?->username ?? (session('user.username') ?? 'Usuário');
  @endphp

  <!-- Top-right chip (avatar + username) -->
  <div class="topbar">
    <a class="user-chip" href="{{ route('profile.edit') }}">
      <img class="avatar"
           src="{{ $avatarUrl }}"
           alt="Foto de perfil"
           onerror="this.onerror=null;this.src='{{ asset('assets/img/avatar-default.png') }}';">
      <span>{{ $username }}</span>
    </a>
  </div>

  <div class="custom-card-container">
    <main class="card p-4 shadow" style="width: 100%; max-width: 400px;">
      <div class="text-center mb-4">
        <h1 class="card-title">Painel</h1>
        <p class="text-muted">Opções de Gerenciamento</p>
      </div>

      <div class="d-grid gap-2">
        <a href="{{ route('economies.create') }}" class="btn btn-outline-secondary">Enviar Economias</a>
        <a href="{{ route('economies.show') }}" class="btn btn-outline-secondary">Minhas Economias</a>
        <a href="{{ route('gastos.create') }}" class="btn btn-outline-secondary">Enviar Gastos</a>
        <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Meus Gastos</a>
        <a href="{{route('economies.saldo')}}" class="btn btn-outline-secondary">Saldo</a>
        <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">Analytics</a>
        <a href="{{ route('projecoes.index') }}" class="btn btn-outline-secondary">Projeções Futuras</a>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Perfil</a>
      </div>
    </main>
  </div>
</body>
</html>
