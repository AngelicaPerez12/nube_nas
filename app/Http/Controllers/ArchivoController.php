<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoController extends Controller
{
    private function resolverEspacio(Request $request, string $espacio): array
    {
        $usuario = $request->user();

        if ($espacio === 'grupo') {
            abort_unless($usuario->grupo_id && $usuario->grupo, 403, 'No tienes un grupo asignado.');
            $raiz = $usuario->grupo->carpeta;
            $puedeEscribir = $usuario->permiso === 'escritura';
        } else {
            $espacio = 'privado';
            $raiz = 'usuario_'.$usuario->id;
            $puedeEscribir = true;
        }

        if (! Storage::disk('nas')->exists($raiz)) {
            Storage::disk('nas')->makeDirectory($raiz);
        }

        return [$espacio, $raiz, $puedeEscribir];
    }

    private function validarRutaRelativa(string $ruta): void
    {
        abort_unless(mb_strlen($ruta) <= 2048, 400);

        if ($ruta === '') {
            return;
        }

        foreach (explode('/', $ruta) as $segmento) {
            abort_if(
                $segmento === '' ||
                $segmento === '.' ||
                $segmento === '..' ||
                str_contains($segmento, '\\') ||
                preg_match('/[\x00-\x1F\x7F]/', $segmento),
                400
            );
        }
    }

    /**
     * Retorna el icono y color de Bootstrap según la extensión del archivo.
     *
     * @return array{icono: string, color: string}
     */
    private function obtenerIconoArchivo(string $extension): array
    {
        return match ($extension) {
            'pdf' => ['icono' => 'bi-file-earmark-pdf-fill', 'color' => 'text-danger'],
            'doc', 'docx', 'odt', 'rtf' => ['icono' => 'bi-file-earmark-word-fill', 'color' => 'text-primary'],
            'xls', 'xlsx', 'csv', 'ods' => ['icono' => 'bi-file-earmark-excel-fill', 'color' => 'text-success'],
            'ppt', 'pptx', 'odp' => ['icono' => 'bi-file-earmark-ppt-fill', 'color' => 'text-warning'],
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico' => ['icono' => 'bi-file-earmark-image-fill', 'color' => 'text-info'],
            'mp4', 'mkv', 'avi', 'mov', 'wmv', 'webm', 'flv' => ['icono' => 'bi-file-earmark-play-fill', 'color' => 'text-danger'],
            'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac' => ['icono' => 'bi-file-earmark-music-fill', 'color' => 'text-primary'],
            'zip', 'rar', '7z', 'tar', 'gz', 'bz2' => ['icono' => 'bi-file-earmark-zip-fill', 'color' => 'text-secondary'],
            'txt', 'log', 'md' => ['icono' => 'bi-file-earmark-text-fill', 'color' => 'text-secondary'],
            'php', 'js', 'ts', 'html', 'css', 'json', 'xml', 'py', 'sh', 'sql', 'c', 'cpp', 'java', 'cs' => ['icono' => 'bi-file-earmark-code-fill', 'color' => 'text-dark'],
            'exe', 'msi', 'apk', 'deb', 'iso' => ['icono' => 'bi-file-earmark-binary-fill', 'color' => 'text-secondary'],
            default => ['icono' => 'bi-file-earmark-fill', 'color' => 'text-secondary'],
        };
    }

    public function index(Request $request)
    {
        $espacioSolicitado = $request->query('espacio', 'privado');
        [$espacio, $raiz, $puedeEscribir] = $this->resolverEspacio($request, $espacioSolicitado);

        $carpeta = $request->query('carpeta', '');
        abort_unless(is_string($carpeta), 400);
        $this->validarRutaRelativa($carpeta);

        $anterior = '';
        if ($carpeta !== '') {
            $segmentos = explode('/', $carpeta);
            array_pop($segmentos);
            $anterior = implode('/', $segmentos);
        }

        $rutaCompleta = $carpeta === '' ? $raiz : $raiz.'/'.$carpeta;

        try {
            $archivos = [];

            foreach (Storage::disk('nas')->directories($rutaCompleta) as $dir) {
                $nombre = basename($dir);
                $archivos[] = [
                    'nombre' => $nombre,
                    'tipo' => 'carpeta',
                    'ruta' => ltrim($carpeta.'/'.$nombre, '/'),
                ];
            }

            foreach (Storage::disk('nas')->files($rutaCompleta) as $file) {
                $nombre = basename($file);
                $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                $archivos[] = [
                    'nombre' => $nombre,
                    'tipo' => 'archivo',
                    'ruta' => ltrim($carpeta.'/'.$nombre, '/'),
                    'extension' => $extension,
                    'icono' => $this->obtenerIconoArchivo($extension),
                ];
            }

            $error = null;
        } catch (\Throwable $e) {
            $archivos = [];
            $error = 'No se pudo cargar esta carpeta del almacenamiento.';
        }

        return view('panel', compact(
            'archivos',
            'carpeta',
            'anterior',
            'error',
            'espacio',
            'puedeEscribir'
        ));
    }

    public function subir(Request $request)
    {
        $datos = $request->validate([
            'archivo' => ['required', 'file', 'max:51200'],
            'carpeta' => ['nullable', 'string', 'max:2048'],
            'espacio' => ['nullable', 'in:privado,grupo'],
        ], [
            'archivo.max' => 'El archivo supera el tamaño máximo permitido de 50 MB por archivo.',
        ]);

        [$espacio, $raiz, $puedeEscribir] = $this->resolverEspacio($request, $datos['espacio'] ?? 'privado');
        abort_unless($puedeEscribir, 403, 'Solo tienes permiso de lectura en este grupo.');

        $carpeta = $datos['carpeta'] ?? '';
        $this->validarRutaRelativa($carpeta);

        $destino = route('panel', ['espacio' => $espacio, 'carpeta' => $carpeta]);
        $rutaCarpeta = $carpeta === '' ? $raiz : $raiz.'/'.$carpeta;

        try {
            $archivo = $request->file('archivo');

            if ($espacio === 'privado') {
                $usuario = $request->user();
                $nuevoTamano = $archivo->getSize();
                $espacioUsado = $usuario->espacioUsadoBytes();
                $limite = $usuario->limiteEspacioBytes();

                if (($espacioUsado + $nuevoTamano) > $limite) {
                    return redirect($destino)->withErrors([
                        'archivo' => 'No tienes suficiente espacio disponible. Límite: '.$usuario->limiteEspacioTexto().' (en uso: '.$usuario->espacioUsadoTexto().').',
                    ]);
                }
            }

            $nombreOriginal = basename($archivo->getClientOriginalName());

            abort_if(
                $nombreOriginal === '' ||
                $nombreOriginal === '.' ||
                $nombreOriginal === '..' ||
                preg_match('/[\x00-\x1F\x7F]/', $nombreOriginal),
                400
            );

            $rutaFinal = $rutaCarpeta.'/'.$nombreOriginal;

            if (Storage::disk('nas')->exists($rutaFinal)) {
                return redirect($destino)->withErrors([
                    'archivo' => 'Ya existe un archivo con ese nombre en esta carpeta.',
                ]);
            }

            $archivo->storeAs($rutaCarpeta, $nombreOriginal, 'nas');

            return redirect($destino)->with('success', 'Archivo subido correctamente.');
        } catch (\Throwable $e) {
            return redirect($destino)->withErrors([
                'archivo' => 'No se pudo subir el archivo.',
            ]);
        }
    }

    public function descargar(Request $request)
    {
        [$espacio, $raiz] = $this->resolverEspacio($request, $request->query('espacio', 'privado'));

        $rutaArchivo = $request->query('archivo');
        abort_unless(is_string($rutaArchivo) && $rutaArchivo !== '', 400);
        $this->validarRutaRelativa($rutaArchivo);

        $rutaCompleta = $raiz.'/'.$rutaArchivo;
        abort_unless(Storage::disk('nas')->exists($rutaCompleta), 404);

        $nombre = basename($rutaArchivo);

        return Storage::disk('nas')->download(
            $rutaCompleta,
            Str::ascii($nombre) ?: 'archivo'
        );
    }

    public function eliminar(Request $request)
    {
        $datos = $request->validate([
            'archivo' => ['required', 'string', 'max:2048'],
            'espacio' => ['nullable', 'in:privado,grupo'],
        ]);

        [$espacio, $raiz, $puedeEscribir] = $this->resolverEspacio($request, $datos['espacio'] ?? 'privado');
        abort_unless($puedeEscribir, 403, 'Solo tienes permiso de lectura en este grupo.');

        $rutaArchivo = $datos['archivo'];
        $this->validarRutaRelativa($rutaArchivo);

        $rutaCompleta = $raiz.'/'.$rutaArchivo;

        if (Storage::disk('nas')->exists($rutaCompleta)) {
            Storage::disk('nas')->delete($rutaCompleta);

            return back()->with('eliminado', 'Archivo eliminado correctamente.');
        }

        return back()->withErrors([
            'archivo' => 'El archivo no existe o ya fue eliminado.',
        ]);
    }
}
