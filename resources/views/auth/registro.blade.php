<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Mi Nube</title>
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
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <a href="{{ route('inicio') }}" class="text-decoration-none text-primary fw-bold fs-4 d-inline-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cloud-fill"></i> Mi Nube
                            </a>
                            <h1 class="h4 fw-bold text-dark mb-1">Crear cuenta</h1>
                            <p class="text-muted small mb-0">Regístrate para obtener tu espacio privado</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('registro') }}">
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
                                           placeholder="Elige un usuario"
                                           required
                                           autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold small">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           minlength="5"
                                           placeholder="Mínimo 5 caracteres"
                                           required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold small">Confirmar contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           minlength="5"
                                           placeholder="Repite tu contraseña"
                                           required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                    <i class="bi bi-person-plus-fill me-1"></i> Crear mi cuenta
                                </button>
                            </div>
                        </form>

                        <p class="text-center text-muted small mb-0">
                            ¿Ya tienes cuenta?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Iniciar sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>