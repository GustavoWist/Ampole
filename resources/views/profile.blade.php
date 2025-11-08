<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Meu Perfil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/bootstrap.min.css') }}">
  <style>
    body { background: #30373F; }
    .card { border-radius: 14px; }
    .avatar-lg { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; }
  </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="text-light h4 m-0">Meu Perfil</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-light">Voltar</a>
            <a href="{{ url('logout') }}" class="btn btn-danger">Sair</a>
        </div>
    </div>


  @php
    use Illuminate\Support\Facades\Storage;
    $avatarUrl = $user->avatar ? Storage::url($user->avatar) : asset('assets/img/avatar-default.png');
  @endphp

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card p-3">
        <h5>Dados do perfil</h5>
        @if(session('success'))
          <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data" class="mt-2">
          @csrf

          <div class="d-flex align-items-center gap-3 mb-3">
            <img id="preview" class="avatar-lg border" src="{{ $avatarUrl }}" alt="Avatar">
            <div>
              <label class="form-label mb-1">Foto (jpg, png, webp)</label>
              <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
              @error('avatar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Nome de usuário</label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
            @error('username') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          <button class="btn btn-primary">Salvar alterações</button>
        </form>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card p-3">
        <h5>Alterar senha</h5>
        @if(session('pwdSuccess'))
          <div class="alert alert-success mt-2">{{ session('pwdSuccess') }}</div>
        @endif
        @if(session('pwdError'))
          <div class="alert alert-danger mt-2">{{ session('pwdError') }}</div>
        @endif
        <form action="{{ route('profile.password') }}" method="post" class="mt-2">
          @csrf
          <div class="mb-3">
            <label class="form-label">Senha atual</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input type="password" name="password" class="form-control" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirme a nova senha</label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
          </div>
          <button class="btn btn-secondary">Atualizar senha</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('avatar')?.addEventListener('change', (e) => {
  const [file] = e.target.files || [];
  if (file) {
    const img = document.getElementById('preview');
    img.src = URL.createObjectURL(file);
  }
});
</script>
</body>
</html>
