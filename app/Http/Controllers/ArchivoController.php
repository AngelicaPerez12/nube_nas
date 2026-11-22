<?php

namespace App\Http\Controllers;

use App\Services\OwnCloudService;
use Illuminate\Http\Request;

class ArchivoController extends Controller
{
    public function index(Request $request, OwnCloudService $owncloud) {
        $usuario = $request->user();

        if (
            !$usuario->owncloud_username ||
            !$usuario->owncloud_app_password
        ) {
            return redirect()->route('owncloud.conectar');
        }

        $carpeta = $request->query('carpeta', '');

        abort_unless(
            is_string($carpeta) && mb_strlen($carpeta) <= 2048,
            400
        );

        if ($carpeta !== '') {
            foreach (explode('/', $carpeta) as $segmento) {
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

        $anterior = '';

        if ($carpeta !== '') {
            $segmentos = explode('/', $carpeta);
            array_pop($segmentos);
            $anterior = implode('/', $segmentos);
        }

        try {
            $archivos = $owncloud->obtenerArchivosUsuario(
                $usuario->owncloud_username,
                $usuario->owncloud_app_password,
                $carpeta
            );

            $error = null;
        } catch (\Throwable $e) {
            $archivos = [];
            $error = 'No se pudo cargar esta carpeta de ownCloud.';
        }

        return view('panel', compact(
            'archivos',
            'carpeta',
            'anterior',
            'error'
        ));
    }

    public function subir(
            Request $request,
            OwnCloudService $owncloud
        ) {
            $usuario = $request->user();

            abort_unless(
                $usuario->owncloud_username &&
                $usuario->owncloud_app_password,
                403
            );

            $datos = $request->validate([
                'archivo' => ['required', 'file', 'max:10240'],
                'carpeta' => ['nullable', 'string', 'max:2048'],
            ]);

            $carpeta = $datos['carpeta'] ?? '';

            $destino = route('panel', [
                'carpeta' => $carpeta,
            ]);

            try {
                $owncloud->subirArchivoUsuario(
                    $usuario->owncloud_username,
                    $usuario->owncloud_app_password,
                    $carpeta,
                    $request->file('archivo')
                );

                return redirect($destino)
                    ->with('success', 'Archivo subido correctamente.');
            } catch (\InvalidArgumentException $e) {
                return redirect($destino)
                    ->withErrors(['archivo' => 'Nombre o ruta no válida.']);
            } catch (\Throwable $e) {
                return redirect($destino)
                    ->withErrors([
                        'archivo' => 'No se pudo subir el archivo. '
                            . 'Comprueba que no exista otro con el mismo nombre.',
                    ]);
            }
        }

    public function descargar(
        Request $request,
        OwnCloudService $owncloud
    ) {
        $usuario = $request->user();

        abort_unless(
            $usuario->owncloud_username &&
            $usuario->owncloud_app_password,
            403
        );

        $rutaArchivo = $request->query('archivo');

        abort_unless(
            is_string($rutaArchivo) &&
            $rutaArchivo !== '' &&
            mb_strlen($rutaArchivo) <= 2048,
            400
        );

        $segmentos = explode('/', $rutaArchivo);

        foreach ($segmentos as $segmento) {
            abort_if(
                $segmento === '' ||
                $segmento === '.' ||
                $segmento === '..' ||
                str_contains($segmento, '\\') ||
                preg_match('/[\x00-\x1F\x7F]/', $segmento),
                400
            );
        }

        try {
            $temporal = $owncloud->descargarArchivoUsuario(
                $usuario->owncloud_username,
                $usuario->owncloud_app_password,
                $rutaArchivo
            );

            $nombre = basename($rutaArchivo);

            return response()
                ->download(
                    $temporal,
                    \Illuminate\Support\Str::ascii($nombre) ?: 'archivo'
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            return back()->withErrors([
                'archivo' => 'No se pudo descargar el archivo.'
            ]);
        }
    }

}
