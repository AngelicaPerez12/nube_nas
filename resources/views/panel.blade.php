<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi unidad | Mi Nube</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fc;
            color: #1e293b;
        }
        header {
            padding: 20px 7%;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { color: #2563eb; font-size: 23px; font-weight: bold; }
        main { max-width: 1100px; margin: 45px auto; padding: 20px; }
        .card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 5px 25px #00000008;
        }
        .files {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 18px;
            margin-top: 25px;
        }
        .file {
            padding: 23px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }
        .file-icon { font-size: 35px; margin-bottom: 12px; }
        .type { color: #64748b; font-size: 13px; }
        button {
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            border: 0;
            border-radius: 9px;
            cursor: pointer;
        }
        .error { color: #be123c; }
    </style>
</head>
<body>
<header>
    <div class="logo">☁ Mi Nube</div>

    <form action="{{ route('salir') }}" method="POST">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</header>

<main>
    <h1>¡Bienvenido, {{ auth()->user()->name }}!</h1>
    <p>Gestiona tus archivos almacenados en la Raspberry Pi 5.</p>

    <section class="card">
        <h2>📁 Mi unidad</h2>

        @if (session('success'))
            <p style="color: #15803d;">
                {{ session('success') }}
            </p>
        @endif

        @if ($errors->any())
            <p style="color: #be123c;">
                {{ $errors->first() }}
            </p>
        @endif

        <form
            action="{{ route('archivos.subir') }}"
            method="POST"
            enctype="multipart/form-data"
            style="margin: 25px 0;"
        >
            @csrf

            <input
                type="hidden"
                name="carpeta"
                value="{{ $carpeta }}"
            >

            <label for="archivo">
                Seleccionar archivo:
            </label>

            <input
                type="file"
                id="archivo"
                name="archivo"
                required
                style="display: block; margin: 12px 0;"
            >

            <button type="submit">
                ⬆ Subir archivo
            </button>
        </form>


        @if ($carpeta !== '')
            <p>
                <a href="{{ route('panel', ['carpeta' => $anterior]) }}"
                style="color: #2563eb; text-decoration: none;">
                    ← Volver
                </a>
            </p>

            <p style="color: #64748b;">
                Ubicación: Mi unidad / {{ $carpeta }}
            </p>
        @endif


        @if ($error)
            <p class="error">{{ $error }}</p>
        @elseif (count($archivos) === 0)
            <p>Tu unidad está vacía.</p>
        @else
            <div class="files">
            @foreach ($archivos as $archivo)

                @if ($archivo['tipo'] === 'carpeta')
                    <a href="{{ route('panel', ['carpeta' => $archivo['ruta']]) }}"
                    class="file"
                    style="text-decoration: none; color: inherit; display: block;">

                        <div class="file-icon">📁</div>
                        <strong>{{ $archivo['nombre'] }}</strong>
                        <p class="type">Carpeta · Abrir</p>
                    </a>
                
                @else
                    <a
                        href="{{ route('archivos.descargar', ['archivo' => $archivo['ruta']]) }}"
                        class="file"
                        style="text-decoration: none; color: inherit; display: block;"
                    >
                        <div class="file-icon">📄</div>

                        <strong>{{ $archivo['nombre'] }}</strong>

                        <p class="type">⬇ Descargar archivo</p>
                    </a>
                @endif


            @endforeach

            </div>
        @endif
    </section>
</main>
</body>
</html>
