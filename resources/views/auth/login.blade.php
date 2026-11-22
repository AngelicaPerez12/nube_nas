<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Mi Nube</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f6fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <a href="{{ route('inicio') }}" class="text-decoration-none text-primary fw-bold fs-4 d-inline-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cloud-fill"></i> Mi Nube
                            </a>
                            <h1 class="h4 fw-bold text-dark mb-1">Bienvenido</h1>
                            <p class="text-muted small mb-0">Inicia sesión para acceder a tus archivos</p>
                        </div>

                        @error('name')
                            <div class="alert alert-danger py-2 small" role="alert">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                            </div>
                        @enderror

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold small">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Tu nombre de usuario"
                                           required
                                           autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold small">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           placeholder="••••••••"
                                           required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                                </button>
                            </div>
                        </form>

                        <p class="text-center text-muted small mb-0">
                            ¿No tienes cuenta?
                            <a href="{{ route('registro') }}" class="text-decoration-none fw-semibold">Crear cuenta</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>