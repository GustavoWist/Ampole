<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Prohibited</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="{{ asset('assets/css/prohibited.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <div class="container">
    <!-- BLOCO 1: Registro visível / Login oculto -->
    <div class="content first-content">
      <div class="first-column">
        <h2 class="title title-primary">Bem vindo de volta!</h2>
        <p class="description description-primary">Se já possui uma conta</p>
        <p class="description description-primary">Por favor, entre com suas informações pessoais</p>
        <button id="signin" class="btn btn-primary">Entrar</button>
      </div>

      <div class="second-column">
        <h2 class="title title-second">Criar conta</h2>

        <div class="social-media">
          <ul class="list-social-media">
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-google-plus"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
          </ul>
        </div>

        <p class="description description-second">Ou utilize o seu e-mail para registrar-se</p>

        <!-- FORM DE REGISTRO -->
        <form class="form" id="registerForm" action="{{ route('register.submit') }}" method="post" novalidate>
          @csrf

          <label class="label-input">
            <i class="fa-solid fa-user icon-modify"></i>
            <input
              type="text"
              id="reg-username"
              name="username"
              value="{{ old('username') }}"
              required
              placeholder="Nome de usuário">
          </label>
          @error('username')
            <div class="text-danger" style="margin-top:-8px;margin-bottom:8px;">{{ $message }}</div>
          @enderror

          <label class="label-input">
            <i class="fa-solid fa-envelope icon-modify"></i>
            <input
              type="email"
              id="reg-email"
              name="email"
              value="{{ old('email') }}"
              required
              placeholder="E-mail">
          </label>
          @error('email')
            <div class="text-danger" style="margin-top:-8px;margin-bottom:8px;">{{ $message }}</div>
          @enderror

          <label class="label-input">
            <i class="fa-solid fa-lock icon-modify"></i>
            <input
              type="password"
              id="reg-password"
              name="password"
              required
              placeholder="Senha (mín. 8)">
          </label>
          @error('password')
            <div class="text-danger" style="margin-top:-8px;margin-bottom:8px;">{{ $message }}</div>
          @enderror

          <label class="label-input">
            <i class="fa-solid fa-lock icon-modify"></i>
            <input
              type="password"
              id="reg-confirm"
              name="password_confirmation"
              required
              placeholder="Confirme a senha">
          </label>

          <button class="btn btn-second" type="submit">Registrar</button>
        </form>
      </div>
    </div>

    <!-- BLOCO 2: Login visível / Registro oculto -->
    <div class="content second-content">
      <div class="first-column">
        <h2 class="title title-primary">Eaí, amigo!</h2>
        <p class="description description-primary">Insira seus dados pessoais</p>
        <p class="description description-primary">E comece sua jornada conosco</p>
        <button id="signup" class="btn btn-primary">Registrar-se</button>
      </div>

      <div class="second-column">
        <h2 class="title title-second">Entrar</h2>

        <div class="social-media">
          <ul class="list-social-media">
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-google-plus"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
          </ul>
        </div>

        <p class="description description-second">Utilize o seu nome de usuário para se conectar</p>

        <!-- ALERTA DE ERRO DE LOGIN (FLASH) -->
        @if(session('loginError'))
          <div class="text-danger" style="margin-bottom:10px;">{{ session('loginError') }}</div>
        @endif

        <!-- FORM DE LOGIN -->
        <form class="form" action="{{ url('loginSubmit') }}" method="post" novalidate>
          @csrf

          <label class="label-input">
            <i class="fa-solid fa-user icon-modify"></i>
            <input
              type="text"
              id="login-username"
              name="login-username"
              value="{{ old('login-username') }}"
              required
              placeholder="Nome de usuário">
          </label>
          @error('login-username')
            <div class="text-danger" style="margin-top:-8px;margin-bottom:8px;">{{ $message }}</div>
          @enderror

          <label class="label-input">
            <i class="fa-solid fa-lock icon-modify"></i>
            <input
              type="password"
              id="login-password"
              name="login-password"
              value="{{ old('login-password') }}"
              required
              placeholder="Senha">
          </label>
          @error('login-password')
            <div class="text-danger" style="margin-top:-8px;margin-bottom:8px;">{{ $message }}</div>
          @enderror

          <button class="btn btn-second" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/js/animation.js') }}"></script>
</body>
</html>
