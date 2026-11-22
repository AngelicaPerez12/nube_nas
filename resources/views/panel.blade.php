<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi unidad | Mi Nube</title>
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f6fc;
            color: #1e293b;
        }
        .file-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }
        .file-title {
            word-break: break-word;
        }
    </style>
</head>
<body>

    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center gap-2" href="{{ route('panel') }}">
                <i class="bi bi-cloud-fill"></i> Mi Nube
            </a>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <span class="text-muted small d-none d-sm-inline">
                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                </span>

                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.index') }}" class="btn btn-dark btn-sm">
                        <i class="bi bi-shield-lock me-1"></i> Panel Admin
                    </a>
                @endif

                <form action="{{ route('salir') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-5" style="max-width: 1100px;">
        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">¡Bienvenido, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">Gestiona tus archivos almacenados en la Raspberry Pi 5.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">

                <!-- Pestañas de Espacio Privado vs Grupo -->
                <ul class="nav nav-pills mb-4 gap-2">
                    <li class="nav-item">
                        <a class="nav-link {{ $espacio === 'privado' ? 'active' : 'bg-light text-dark' }}"
                           href="{{ route('panel', ['espacio' => 'privado']) }}">
                            <i class="bi bi-person-lock me-1"></i> Mi unidad privada
                        </a>
                    </li>
                    @if(auth()->user()->grupo)
                        <li class="nav-item">
                            <a class="nav-link {{ $espacio === 'grupo' ? 'active' : 'bg-light text-dark' }}"
                               href="{{ route('panel', ['espacio' => 'grupo']) }}">
                                <i class="bi bi-people-fill me-1"></i> Grupo: {{ auth()->user()->grupo->nombre }}
                                <span class="badge {{ auth()->user()->permiso === 'escritura' ? 'bg-success' : 'bg-secondary' }} ms-1">
                                    {{ auth()->user()->permiso === 'escritura' ? 'Lectura/Escritura' : 'Solo lectura' }}
                                </span>
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
                        @if($espacio === 'grupo' && auth()->user()->grupo)
                            <i class="bi bi-people text-primary fs-4"></i> Carpeta compartida: {{ auth()->user()->grupo->nombre }}
                        @else
                            <i class="bi bi-folder2-open text-primary fs-4"></i> Mi unidad privada
                        @endif
                    </h2>

                    @if ($carpeta !== '')
                        <a href="{{ route('panel', ['espacio' => $espacio, 'carpeta' => $anterior]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Volver atrás
                        </a>
                    @endif
                </div>

                <!-- Alertas de éxito o error -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <!-- Formulario para subir archivos (visible solo si tiene permiso de escritura) -->
                @if ($puedeEscribir)
                    <div class="bg-light p-3 p-md-4 rounded-3 border mb-4">
                        <form action="{{ route('archivos.subir') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="carpeta" value="{{ $carpeta }}">
                            <input type="hidden" name="espacio" value="{{ $espacio }}">

                            <label for="archivo" class="form-label fw-semibold">
                                <i class="bi bi-cloud-arrow-up text-primary me-1"></i> Subir un nuevo archivo
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="archivo" name="archivo" required>
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="bi bi-upload me-1"></i> Subir
                                </button>
                            </div>
                            <div class="form-text">Tamaño máximo permitido: 50 MB.</div>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="bi bi-eye-fill me-1"></i> Tienes permiso de <strong>solo lectura</strong> en este grupo. Puedes ver y descargar archivos, pero no subir ni eliminar.
                    </div>
                @endif

                <!-- Ruta actual (Breadcrumb) -->
                @if ($carpeta !== '')
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb bg-light px-3 py-2 rounded-2 mb-0 small">
                            <li class="breadcrumb-item">
                                <a href="{{ route('panel', ['espacio' => $espacio]) }}" class="text-decoration-none">
                                    {{ $espacio === 'grupo' ? 'Carpeta de grupo' : 'Mi unidad' }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $carpeta }}</li>
                        </ol>
                    </nav>
                @endif

                <!-- Listado de carpetas y archivos -->
                @if ($error)
                    <div class="alert alert-danger">{{ $error }}</div>
                @elseif (count($archivos) === 0)
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                        <p class="mb-0 fw-medium">Esta carpeta está vacía</p>
                        @if($puedeEscribir)
                            <small>Sube tu primer archivo usando el formulario de arriba.</small>
                        @endif
                    </div>
                @else
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mt-1">
                        @foreach ($archivos as $archivo)
                            <div class="col">
                                @if ($archivo['tipo'] === 'carpeta')
                                    <a href="{{ route('panel', ['espacio' => $espacio, 'carpeta' => $archivo['ruta']]) }}"
                                       class="card h-100 text-decoration-none text-dark border file-card shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <i class="bi bi-folder-fill text-warning fs-1 mb-2 d-block"></i>
                                                <h6 class="card-title fw-semibold file-title mb-1">{{ $archivo['nombre'] }}</h6>
                                            </div>
                                            <span class="text-muted small mt-2">
                                                <i class="bi bi-box-arrow-in-right me-1"></i>Abrir carpeta
                                            </span>
                                        </div>
                                    </a>
                                @else
                                    <div class="card h-100 border file-card shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <i class="bi bi-file-earmark-text-fill text-primary fs-1 mb-2 d-block"></i>
                                                <h6 class="card-title fw-semibold file-title mb-3">{{ $archivo['nombre'] }}</h6>
                                            </div>

                                            <div class="d-flex gap-2 pt-2 border-top">
                                                <a href="{{ route('archivos.descargar', ['espacio' => $espacio, 'archivo' => $archivo['ruta']]) }}"
                                                   class="btn btn-outline-primary btn-sm flex-grow-1"
                                                   title="Descargar archivo">
                                                    <i class="bi bi-download me-1"></i> Bajar
                                                </a>

                                                @if ($puedeEscribir)
                                                    <form action="{{ route('archivos.eliminar') }}"
                                                          method="POST"
                                                          onsubmit="return confirm('¿Seguro que deseas eliminar {{ addslashes($archivo['nombre']) }}?');"
                                                          class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="archivo" value="{{ $archivo['ruta'] }}">
                                                        <input type="hidden" name="espacio" value="{{ $espacio }}">
                                                        <button type="submit"
                                                                class="btn btn-outline-danger btn-sm"
                                                                title="Eliminar archivo">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>