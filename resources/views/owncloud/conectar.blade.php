
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conectar ownCloud | Mi Nube</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fc;
            color: #1e293b;
        }
        .card {
            max-width: 480px;
            margin: 75px auto;
            padding: 35px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px #00000010;
        }
        h1 { color: #2563eb; }
        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .error { color: #be123c; }
        .help { color: #64748b; line-height: 1.6; }
    </style>
</head>
<body>
    <main class="card">
        <h1>☁ Conectar ownCloud</h1>

        <p class="help">
            Vincula tu cuenta para acceder a tus archivos
            almacenados en la Raspberry Pi 5.
        </p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('owncloud.conectar') }}">
            @csrf

            <label>Usuario de ownCloud</label>
            <input
                name="owncloud_username"
                value="{{ old('owncloud_username') }}"
                required
                autocomplete="username"
            >

            <label>Contraseña de aplicación</label>
            <input
                type="password"
                name="app_password"
                required
                autocomplete="off"
            >

            <button type="submit">Conectar mi nube</button>
        </form>

        <p class="help">
            Utiliza una contraseña de aplicación, no tu
            contraseña principal de ownCloud.
        </p>
    </main>
</body>
</html>
