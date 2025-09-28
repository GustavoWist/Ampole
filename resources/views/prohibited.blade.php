<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Prohibited</title>
  <link rel="stylesheet" href="{{asset('assets/css/prohibited.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <div class="container">
    <div class="content first-content">
      <div class="first-column">
        <h2 class="title title-primary">Bem vindo de volta!</h2>
        <p class="description description-primary">Se já possui uma conta</p>
        <p class="description description-primary">Por favor entre com suas informações pessoais</p>
        <button id="signin" class="btn btn-primary">Entrar</button>
      </div><!-- first-column -->
      <div class="second-column">
        <h2 class="title title-second">Criar conta</h2>
        <div class="social-media">
          <ul class="list-social-media">
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-google-plus"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
          </ul>
        </div><!-- midias sociais -->
        <p class="description description-second">Ou utilize o seu email para registrar-se</p>
        <form class ="form" id="registerForm">
          <label class="label-input" for="">
            <i class="fa-solid fa-user icon-modify"></i>
            <input type="text" id="reg-username" required placeholder="Nome"><br>
          </label>
          <label class="label-input" for="">
            <i class="fa-solid fa-envelope icon-modify"></i>
            <input type="email" id="reg-email" required placeholder="E-mail"><br>
          </label>
          <label class="label-input" for="">
            <i class="fa-solid fa-lock icon-modify"></i>
            <input type="password" id="reg-password" required placeholder="Senha"><br>
          </label>
          <label class="label-input" for="">
            <i class="fa-solid fa-lock icon-modify"></i>
            <input type="password" id="reg-confirm" required placeholder="Confirme a senha"><br>
          </label>
          
          <button class="btn btn-second" type="submit">Registrar</button>
        </form>
      </div><!-- second-column -->
    </div><!-- first-content -->
    <div class="content second-content">
      <div class="first-column">
        <h2 class="title title-primary">Eaí, amigo!</h2>
        <p class="description description-primary">Insira seus dados pessoas</p>
        <p class="description description-primary">E comece sua jornada conosco</p>
        <button id="signup" class="btn btn-primary">Registrar-se</button>
      </div><!-- first-column -->
      <div class="second-column">
        <h2 class="title title-second">Entrar</h2>
        <div class="social-media">
          <ul class="list-social-media">
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-google-plus"></i></a></li>
            <li class="item-social-media"><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
          </ul>
        </div><!-- midias sociais -->
        <p class="description description-second">Utilize o seu nome de usuário para se conectar</p>
        <form class="form" action="{{ url('loginSubmit')}}" method="post">
            @csrf
            <label for="login-username" class="label-input">
                <i class="fa-solid fa-user icon-modify"></i>
                <input type="text" id="login-username" name="login-username" value="{{ old('login-username') }}" required placeholder="nome"><br>
                @error('text_username')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </label>
        
    
            <label for="login-password" class="label-input" for="">
                <i class="fa-solid fa-lock icon-modify"></i>
                <input type="password" name="login-password" value="{{ old('login-password') }}" required placeholder="senha"><br>
                @error('text_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </label>
            
            <button class="btn btn-second" type="submit">Entrar</button>
        </form>
      </div><!-- second-column -->
    </div><!-- second-content -->
  </div>
    <script src="{{ asset('assets/js/animation.js') }}"></script>
</body>
</html>

