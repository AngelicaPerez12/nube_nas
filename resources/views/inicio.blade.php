<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Nube | NAS Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f6fc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>

    <!-- Barra superior -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center gap-2" href="{{ route('inicio') }}">
                <i class="bi bi-cloud-fill"></i> Mi Nube
            </a>

            <div class="d-flex gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm px-3">Iniciar sesión</a>
                    <a href="{{ route('registro') }}" class="btn btn-primary btn-sm px-3">Crear cuenta</a>
                @endguest

                @auth
                    <a href="{{ route('panel') }}" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-folder2-open me-1"></i> Ir a mi unidad
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Sección principal (Hero) -->
    <main class="container my-auto py-5 text-center" id="inicio" style="max-width: 760px;">
        <div class="mb-4">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold mb-3">
                <i class="bi bi-hdd-network me-1"></i> Almacenamiento NAS Personal
            </span>
            <h1 class="display-5 fw-bold text-dark mb-3">
                Tu nube personal, segura y accesible
            </h1>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 600px;">
                Almacena tus fotografías, documentos y archivos desde cualquier lugar, con tu propio espacio privado alojado en Raspberry Pi.
            </p>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
                </a>
                <a href="{{ route('registro') }}" class="btn btn-outline-primary btn-lg px-4 fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Crear cuenta
                </a>
            @endguest

            @auth
                <a href="{{ route('panel') }}" class="btn btn-primary btn-lg px-4 fw-semibold">
                    <i class="bi bi-folder2-open me-2"></i>Ir a mi unidad
                </a>
            @endauth
        </div>

        <!-- Tarjetas de características -->
        <div class="row g-3 text-start mt-2">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body">
                        <i class="bi bi-shield-lock-fill text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Espacio Privado</h6>
                        <p class="text-muted small mb-0">Cada usuario cuenta con su propio directorio aislado y protegido.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body">
                        <i class="bi bi-globe2 text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Acceso Remoto</h6>
                        <p class="text-muted small mb-0">Disponible desde cualquier red externa de forma segura.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body">
                        <i class="bi bi-arrow-left-right text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Gestión Directa</h6>
                        <p class="text-muted small mb-0">Sube, descarga y administra tus documentos al instante.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>