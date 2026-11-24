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
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
    <main class="container mt-3 mb-4" style="max-width: 1100px;">
        <div class="mb-3">
            <h1 class="h4 fw-bold mb-1">¡Bienvenido, {{ auth()->user()->name }}!</h1>
            <p class="text-muted small mb-0">Gestiona tus archivos almacenados en la Raspberry Pi 5.</p>
        </div>

        <!-- Tarjeta de Almacenamiento Asignado -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-hdd-fill fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="h6 fw-bold mb-0">Almacenamiento asignado</h2>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ auth()->user()->limiteEspacioTexto() }}</span>
                            </div>
                            <p class="text-muted small mb-0 mt-1">
                                <i class="bi bi-info-circle me-1 text-primary"></i>Se te ha asignado automáticamente <strong>{{ auth()->user()->limiteEspacioTexto() }}</strong> de almacenamiento personal en la nube.
                            </p>
                        </div>
                    </div>

                    <div style="min-width: 260px;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Usado: <strong>{{ auth()->user()->espacioUsadoTexto() }}</strong></span>
                            <span><strong>{{ auth()->user()->porcentajeUso() }}%</strong></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ auth()->user()->porcentajeUso() >= 90 ? 'bg-danger' : (auth()->user()->porcentajeUso() >= 75 ? 'bg-warning' : 'bg-primary') }}" 
                                 role="progressbar" 
                                 style="width: {{ auth()->user()->porcentajeUso() }}%;" 
                                 aria-valuenow="{{ auth()->user()->porcentajeUso() }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-end small text-muted mt-1" style="font-size: 0.75rem;">
                            Disponible: {{ auth()->user()->formatearBytes(max(0, auth()->user()->limiteEspacioBytes() - auth()->user()->espacioUsadoBytes())) }}
                        </div>
                    </div>
                </div>
            </div>
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

                @if (session('eliminado'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-trash-fill me-2"></i>{{ session('eliminado') }}
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
                        <form id="formSubida" action="{{ route('archivos.subir') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="carpeta" value="{{ $carpeta }}">
                            <input type="hidden" name="espacio" value="{{ $espacio }}">

                            <label for="archivo" class="form-label fw-semibold">
                                <i class="bi bi-cloud-arrow-up text-primary me-1"></i> Subir un nuevo archivo
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="archivo" name="archivo" required>
                                <button id="btnSubir" class="btn btn-primary px-4" type="submit">
                                    <i class="bi bi-upload me-1"></i> Subir
                                </button>
                            </div>
                            <div class="form-text">Tamaño máximo permitido: 50 MB por archivo.</div>
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
                                                <h6 class="card-title fw-semibold file-title text-truncate mb-1" title="{{ $archivo['nombre'] }}">{{ $archivo['nombre'] }}</h6>
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
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <i class="bi {{ $archivo['icono']['icono'] ?? 'bi-file-earmark-fill' }} {{ $archivo['icono']['color'] ?? 'text-secondary' }} fs-1"></i>
                                                    @if(!empty($archivo['extension']))
                                                        <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.65rem;">{{ $archivo['extension'] }}</span>
                                                    @endif
                                                </div>
                                                <h6 class="card-title fw-semibold file-title text-truncate mb-3" title="{{ $archivo['nombre'] }}">{{ $archivo['nombre'] }}</h6>
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

    <!-- Script para evitar ERR_UPLOAD_FILE_CHANGED en navegadores Android y validar límites -->
    <script>
        document.getElementById('formSubida')?.addEventListener('submit', async function (e) {
            const input = document.getElementById('archivo');
            const btn = document.getElementById('btnSubir');
            if (!input || !input.files.length) return;

            const file = input.files[0];
            const maxArchivoBytes = 50 * 1024 * 1024; // 50 MB por archivo
            const espacioActual = '{{ $espacio }}';
            const disponibleBytes = {{ max(0, auth()->user()->limiteEspacioBytes() - auth()->user()->espacioUsadoBytes()) }};

            if (file.size > maxArchivoBytes) {
                e.preventDefault();
                const pesoMb = (file.size / (1024 * 1024)).toFixed(1);
                alert(`El archivo pesa ${pesoMb} MB y supera el tamaño máximo permitido de 50 MB por archivo.`);
                return;
            }

            if (espacioActual === 'privado' && file.size > disponibleBytes) {
                e.preventDefault();
                const pesoMb = (file.size / (1024 * 1024)).toFixed(1);
                const dispMb = (disponibleBytes / (1024 * 1024)).toFixed(1);
                alert(`No tienes suficiente espacio disponible. El archivo pesa ${pesoMb} MB y solo te quedan ${dispMb} MB disponibles.`);
                return;
            }

            e.preventDefault();

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Subiendo...';

            const panelUrl = '{{ route("panel", ["espacio" => $espacio, "carpeta" => $carpeta]) }}';

            try {
                const buffer = await file.arrayBuffer();
                const blob = new Blob([buffer], { type: file.type || 'application/octet-stream' });

                const formData = new FormData(this);
                formData.set('archivo', blob, file.name);

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const html = await response.text();

                if (html && html.includes('<!DOCTYPE html>')) {
                    document.open();
                    document.write(html);
                    document.close();
                    if (response.url) {
                        history.replaceState({}, '', response.url);
                    }
                } else if (response.redirected && response.url && !response.url.includes('/archivos/subir')) {
                    window.location.href = response.url;
                } else {
                    window.location.href = panelUrl;
                }
            } catch (error) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('No se pudo completar la subida. Verifica tu conexión o el tamaño del archivo.');
            }
        });
    </script>
</body>
</html>