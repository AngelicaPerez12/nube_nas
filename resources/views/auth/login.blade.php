
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Mi Nube</title>
    <style>
        body { font-family:Arial; background:#f3f6fc; }
        main { max-width:420px; margin:80px auto; padding:35px;
               background:white; border-radius:15px; }
        input { box-sizing:border-box; width:100%; padding:14px;
                margin:9px 0 20px; border:1px solid #ccc; border-radius:8px; }
        button { width:100%; padding:14px; border:0; border-radius:8px;
                 background:#2563eb; color:white; cursor:pointer; }
        .error { color:#be123c; }
    </style>
</head>
<body>
<main>
    <h1>Bienvenido a Mi Nube</h1>
    <p>Inicia sesión para continuar</p>

    @error('email')
        <p class="error">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label>Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Contraseña</label>
        <input type="password" name="password" required>

        <button type="submit">Iniciar sesión</button>
    </form>

    <p>¿No tienes cuenta? <a href="{{ route('registro') }}">Crear cuenta</a></p>
</main>
</body>
</html>
