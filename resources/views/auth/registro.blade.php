
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Mi Nube</title>
    <style>
        body { font-family: Arial; background:#f3f6fc; }
        main { max-width:420px; margin:70px auto; background:white;
               padding:35px; border-radius:15px; }
        input { box-sizing:border-box; width:100%; padding:13px;
                margin:8px 0 18px; border:1px solid #ccc; border-radius:8px; }
        button { width:100%; padding:14px; background:#2563eb;
                 color:white; border:0; border-radius:8px; cursor:pointer; }
        .error { color:#be123c; }
    </style>
</head>
<body>
<main>
    <h1>Crear cuenta</h1>
    <p>Regístrate en Mi Nube</p>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('registro') }}">
        @csrf

        <label>Nombre completo</label>
        <input name="name" value="{{ old('name') }}" required>

        <label>Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Contraseña (mínimo 12 caracteres)</label>
        <input type="password" name="password" required>

        <label>Confirmar contraseña</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">Crear mi cuenta</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></p>
</main>
</body>
</html>
