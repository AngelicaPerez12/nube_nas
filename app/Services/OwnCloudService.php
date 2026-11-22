<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OwnCloudService
{
    public function comprobarConexion(): array
    {
        $url = rtrim(
            config('services.owncloud.url'),
            '/'
        );

        $respuesta = Http::timeout(10)
            ->withoutRedirecting()
            ->get($url . '/status.php');

        return [
            'http_status' => $respuesta->status(),
            'datos' => $respuesta->json(),
        ];
    }

    public function comprobarWebDav(): array
    {
        $url = rtrim(config('services.owncloud.url'), '/');

        $respuesta = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'Depth' => '0',
            ])
            ->send('PROPFIND', $url . '/remote.php/dav/files/');

        return [
            'http_status' => $respuesta->status(),
            'requiere_autenticacion' => $respuesta->status() === 401,
        ];
    }

    public function listarArchivosPrueba(): array
    {
        $url = rtrim(config('services.owncloud.url'), '/');

        $usuario = config('services.owncloud.test_username');
        $password = config('services.owncloud.test_app_password');

        if (!$usuario || !$password) {
            return ['error' => 'Faltan credenciales de prueba'];
        }

        $respuesta = \Illuminate\Support\Facades\Http::timeout(20)
            ->withBasicAuth($usuario, $password)
            ->withHeaders([
                'Depth' => '1',
                'Content-Type' => 'application/xml',
            ])
            ->withBody(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<d:propfind xmlns:d="DAV:">'
                . '<d:prop><d:displayname/><d:resourcetype/>'
                . '</d:prop></d:propfind>',
                'application/xml'
            )
            ->send(
                'PROPFIND',
                $url . '/remote.php/dav/files/' . rawurlencode($usuario) . '/'
            );

        return [
            'http_status' => $respuesta->status(),
            'conexion_correcta' => $respuesta->status() === 207,
        ];
    }
        
    public function obtenerArchivosPrueba(): array
    {
        $url = rtrim(config('services.owncloud.url'), '/');
        $usuario = config('services.owncloud.test_username');
        $password = config('services.owncloud.test_app_password');

        if (!$usuario || !$password) {
            return ['error' => 'Faltan las credenciales de prueba'];
        }

        $ruta = '/remote.php/dav/files/' . rawurlencode($usuario) . '/';

        $respuesta = \Illuminate\Support\Facades\Http::timeout(20)
            ->withBasicAuth($usuario, $password)
            ->withHeaders(['Depth' => '1'])
            ->withBody(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<d:propfind xmlns:d="DAV:">'
                . '<d:prop><d:displayname/><d:resourcetype/>'
                . '</d:prop></d:propfind>',
                'application/xml'
            )
            ->send('PROPFIND', $url . $ruta);

        if ($respuesta->status() !== 207) {
            return ['http_status' => $respuesta->status()];
        }

        $xml = new \DOMDocument();

        if (!@$xml->loadXML($respuesta->body(), LIBXML_NONET)) {
            return ['error' => 'Respuesta XML no válida'];
        }

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('d', 'DAV:');

        $archivos = [];

        foreach ($xpath->query('//d:response') as $recurso) {
            $href = $xpath->evaluate('string(d:href)', $recurso);

            $rutaRecurso = rawurldecode(
                parse_url($href, PHP_URL_PATH) ?? ''
            );

            // Omitimos la carpeta raíz del usuario.
            if (rtrim($rutaRecurso, '/') === rtrim($ruta, '/')) {
                continue;
            }

            $nombre = $xpath->evaluate(
                'string(d:propstat/d:prop/d:displayname)',
                $recurso
            );

            if ($nombre === '') {
                $nombre = basename(rtrim($rutaRecurso, '/'));
            }

            $esCarpeta = $xpath->query(
                'd:propstat/d:prop/d:resourcetype/d:collection',
                $recurso
            )->length > 0;

            $archivos[] = [
                'nombre' => $nombre,
                'tipo' => $esCarpeta ? 'carpeta' : 'archivo',
            ];
        }

        return [
            'http_status' => 207,
            'archivos' => $archivos,
        ];
    }

    
    public function obtenerArchivosUsuario(
        string $usuario,
        string $password,
        string $carpeta = ''
    ): array {
        $base = rtrim(config('services.owncloud.url'), '/');

        $segmentos = $carpeta === ''
            ? []
            : explode('/', $carpeta);

        foreach ($segmentos as $segmento) {
            if (
                $segmento === '' ||
                $segmento === '.' ||
                $segmento === '..' ||
                str_contains($segmento, '\\') ||
                preg_match('/[\x00-\x1F\x7F]/', $segmento)
            ) {
                throw new \InvalidArgumentException('Ruta no válida.');
            }
        }

        $ruta = '/remote.php/dav/files/'
            . rawurlencode($usuario) . '/';

        if ($segmentos !== []) {
            $ruta .= implode(
                '/',
                array_map('rawurlencode', $segmentos)
            ) . '/';
        }

        $respuesta = \Illuminate\Support\Facades\Http::timeout(20)
            ->withBasicAuth($usuario, $password)
            ->withoutRedirecting()
            ->withHeaders(['Depth' => '1'])
            ->withBody(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<d:propfind xmlns:d="DAV:">'
                . '<d:prop><d:displayname/><d:resourcetype/>'
                . '</d:prop></d:propfind>',
                'application/xml'
            )
            ->send('PROPFIND', $base . $ruta);

        if ($respuesta->status() !== 207) {
            throw new \RuntimeException(
                'ownCloud respondió con HTTP '
                . $respuesta->status()
            );
        }

        $xml = new \DOMDocument();

        if (!@$xml->loadXML($respuesta->body(), LIBXML_NONET)) {
            throw new \RuntimeException('Respuesta XML no válida.');
        }

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('d', 'DAV:');

        $archivos = [];

        foreach ($xpath->query('/d:multistatus/d:response') as $recurso) {
            $href = $xpath->evaluate('string(d:href)', $recurso);

            $rutaArchivo = rawurldecode(
                parse_url($href, PHP_URL_PATH) ?: ''
            );

            if (
                rtrim($rutaArchivo, '/') ===
                rtrim(rawurldecode($ruta), '/')
            ) {
                continue;
            }

            $nombre = $xpath->evaluate(
                'string(d:propstat/d:prop/d:displayname)',
                $recurso
            );

            if (!$nombre) {
                $nombre = basename(rtrim($rutaArchivo, '/'));
            }

            $esCarpeta = $xpath->query(
                'd:propstat/d:prop/d:resourcetype/d:collection',
                $recurso
            )->length > 0;

            $archivos[] = [
                'nombre' => $nombre,
                'tipo' => $esCarpeta ? 'carpeta' : 'archivo',
                'ruta' => ltrim(
                    $carpeta . '/' . $nombre,
                    '/'
                ),
            ];
        }

        return $archivos;
    }

    public function subirArchivoUsuario(
        string $usuario,
        string $password,
        string $carpeta,
        \Illuminate\Http\UploadedFile $archivo
    ): void {
        $segmentos = $carpeta === ''
            ? []
            : explode('/', $carpeta);

        $nombre = $archivo->getClientOriginalName();

        foreach ([...$segmentos, $nombre] as $segmento) {
            if (
                $segmento === '' ||
                $segmento === '.' ||
                $segmento === '..' ||
                str_contains($segmento, '/') ||
                str_contains($segmento, '\\') ||
                preg_match('/[\x00-\x1F\x7F]/', $segmento)
            ) {
                throw new \InvalidArgumentException(
                    'Nombre o ruta de archivo no válido.'
                );
            }
        }

        $ruta = '/remote.php/dav/files/'
            . rawurlencode($usuario) . '/';

        if ($segmentos !== []) {
            $ruta .= implode(
                '/',
                array_map('rawurlencode', $segmentos)
            ) . '/';
        }

        $ruta .= rawurlencode($nombre);

        $url = rtrim(config('services.owncloud.url'), '/')
            . $ruta;

        $stream = fopen($archivo->getRealPath(), 'rb');

        if ($stream === false) {
            throw new \RuntimeException(
                'No se pudo leer el archivo.'
            );
        }

        try {
            $respuesta = \Illuminate\Support\Facades\Http::timeout(120)
                ->connectTimeout(10)
                ->withBasicAuth($usuario, $password)
                ->withoutRedirecting()
                ->withHeaders([
                    'If-None-Match' => '*',
                ])
                ->withBody($stream, 'application/octet-stream')
                ->send('PUT', $url);
        } finally {
            fclose($stream);
        }

        if (!in_array($respuesta->status(), [201, 204], true)) {
            throw new \RuntimeException(
                'ownCloud respondió con HTTP '
                . $respuesta->status()
            );
        }
    }

    public function descargarArchivoUsuario(
        string $usuario,
        string $password,
        string $rutaArchivo
    ): string {
        $segmentos = explode('/', $rutaArchivo);

        foreach ($segmentos as $segmento) {
            if (
                $segmento === '' ||
                $segmento === '.' ||
                $segmento === '..' ||
                str_contains($segmento, '\\') ||
                preg_match('/[\x00-\x1F\x7F]/', $segmento)
            ) {
                throw new \InvalidArgumentException('Ruta no válida.');
            }
        }

        $ruta = '/remote.php/dav/files/'
            . rawurlencode($usuario) . '/'
            . implode('/', array_map('rawurlencode', $segmentos));

        $url = rtrim(config('services.owncloud.url'), '/')
            . $ruta;

        $temporal = tempnam(sys_get_temp_dir(), 'mi_nube_');

        if ($temporal === false) {
            throw new \RuntimeException(
                'No se pudo crear el archivo temporal.'
            );
        }

        try {
            $respuesta = \Illuminate\Support\Facades\Http::timeout(120)
                ->connectTimeout(10)
                ->withBasicAuth($usuario, $password)
                ->withoutRedirecting()
                ->withOptions(['sink' => $temporal])
                ->get($url);

            if (!$respuesta->successful()) {
                throw new \RuntimeException(
                    'ownCloud respondió con HTTP '
                    . $respuesta->status()
                );
            }

            return $temporal;
        } catch (\Throwable $e) {
            @unlink($temporal);
            throw $e;
        }
    }

}
