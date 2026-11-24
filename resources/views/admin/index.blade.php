<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Administrador | Mi Nube</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f3f6fc; color: #1e293b; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center gap-2" href="{{ route('panel') }}">
                <i class="bi bi-cloud-fill"></i> Mi Nube <span class="badge bg-dark fs-6">Admin</span>
            </a>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('panel') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-folder2-open me-1"></i> Volver a mi unidad
                </a>
                <form action="{{ route('salir') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Salir</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container my-5" style="max-width: 1150px;">
        <h1 class="h3 fw-bold mb-1">Administración de Usuarios y Grupos</h1>
        <p class="text-muted mb-4">Crea departamentos, registra usuarios y asigna permisos de lectura o escritura sobre el NAS.</p>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <!-- Crear Grupo -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Crear Grupo / Carpeta</h5>
                        <form action="{{ route('admin.grupos.crear') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nombre del grupo</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Contabilidad, Desarrollo..." required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i> Crear Grupo
                            </button>
                        </form>

                        <hr class="my-4">
                        <h6 class="fw-bold small text-muted text-uppercase mb-3">Grupos activos</h6>
                        @if($grupos->isEmpty())
                            <p class="text-muted small mb-0">Aún no hay grupos creados.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($grupos as $g)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <strong>{{ $g->nombre }}</strong>
                                            <div class="text-muted small">Carpeta: <code>{{ $g->carpeta }}</code></div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">{{ $g->usuarios_count }} usuarios</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Crear Usuario -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-primary me-2"></i>Crear Nuevo Usuario</h5>
                        <form action="{{ route('admin.usuarios.crear') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Usuario</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Contraseña (mín. 5)</label>
                                    <div class="input-group">
                                        <input type="password" id="admin_password" name="password" class="form-control" minlength="5" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="admin_password" title="Mostrar/ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Grupo asignado</label>
                                    <select name="grupo_id" class="form-select">
                                        <option value="">-- Sin grupo (Solo privado) --</option>
                                        @foreach($grupos as $g)
                                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Permiso en el grupo</label>
                                    <select name="permiso" class="form-select">
                                        <option value="lectura">Solo lectura (Ver y descargar)</option>
                                        <option value="escritura">Lectura y escritura (Subir y borrar)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="nuevoAdmin">
                                        <label class="form-check-label small" for="nuevoAdmin">Otorgar rol de Administrador</label>
                                    </div>
                                    <div class="form-text small mt-1">
                                        <i class="bi bi-hdd me-1 text-primary"></i>Al crearse, se le asignará automáticamente <strong>1 GB</strong> de almacenamiento personal.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check2-circle me-1"></i> Registrar usuario
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Usuarios y Asignación de Permisos -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Gestión de Permisos por Usuario</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Almacenamiento</th>
                                <th>Grupo Asignado</th>
                                <th>Permiso en Grupo</th>
                                <th>Admin</th>
                                <th>Guardar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $u)
                                <tr>
                                    <td class="fw-semibold">{{ $u->name }}</td>
                                    <td>
                                        @if($u->is_admin)
                                            <span class="badge bg-dark">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Usuario</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $u->espacioUsadoTexto() }} / {{ $u->limiteEspacioTexto() }}</div>
                                        <div class="progress mt-1" style="height: 5px; width: 100px;">
                                            <div class="progress-bar {{ $u->porcentajeUso() >= 90 ? 'bg-danger' : ($u->porcentajeUso() >= 75 ? 'bg-warning' : 'bg-primary') }}" style="width: {{ $u->porcentajeUso() }}%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="grupo_id" form="form-update-{{ $u->id }}" class="form-select form-select-sm">
                                            <option value="">-- Sin grupo --</option>
                                            @foreach($grupos as $g)
                                                <option value="{{ $g->id }}" @selected($u->grupo_id === $g->id)>
                                                    {{ $g->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="permiso" form="form-update-{{ $u->id }}" class="form-select form-select-sm">
                                            <option value="lectura" @selected($u->permiso === 'lectura')>Solo lectura</option>
                                            <option value="escritura" @selected($u->permiso === 'escritura')>Lectura / Escritura</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="checkbox" form="form-update-{{ $u->id }}" class="form-check-input" name="is_admin" value="1" @checked($u->is_admin)>
                                    </td>
                                    <td>
                                        <form id="form-update-{{ $u->id }}" action="{{ route('admin.usuarios.actualizar', $u) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar cambios">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if(auth()->id() !== $u->id)
                                            <form action="{{ route('admin.usuarios.eliminar', $u) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar al usuario {{ addslashes($u->name) }} y sus archivos privados?');"
                                                  class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar usuario">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-secondary">Actual</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.getAttribute('data-target'));
                if (target) {
                    const isPassword = target.type === 'password';
                    target.type = isPassword ? 'text' : 'password';
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                    }
                }
            });
        });
    </script>
</body>
</html>