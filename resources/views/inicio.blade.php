
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Nube | NAS Personal</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1b2d45;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 8%;
            background: #ffffff;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 13px 22px;
            border-radius: 9px;
            background: #2563eb;
            color: white;
            margin: 5px;
        }
        .btn-secondary {
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
        }
        .hero {
            text-align: center;
            padding: 110px 20px;
        }
        h1 {
            font-size: clamp(34px, 6vw, 52px);
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 30px;
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">☁ Mi Nube</div>
        <nav>
            <a href="#inicio">Inicio</a>
        </nav>
    </header>

    <main class="hero" id="inicio">
        <h1>Tu nube personal, segura y accesible</h1>
        <p>
            Almacena tus fotografías, documentos y archivos
            desde cualquier lugar, con tu propio espacio privado.
        </p>
        
            
        
        @guest
            <a href="{{ route('login') }}" class="btn">Iniciar sesión</a>

            <a href="{{ route('registro') }}" class="btn btn-secondary">Crear cuenta</a>
        @endguest

        @auth
            <a href="{{ route('panel') }}" class="btn">Ir a mi unidad</a>
        @endauth


    </main>
</body>
</html>
