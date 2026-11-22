<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\Rule;

class OwnCloudConnectionController extends Controller
{
    public function mostrar(Request $request)
    {
        if ($request->user()->owncloud_username) {
            return redirect()->route('panel');
        }

        return view('owncloud.conectar');
    }

    public function conectar(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->owncloud_username) {
            return redirect()->route('panel');
        }

        $datos = $request->validate([
            'owncloud_username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'owncloud_username'),
            ],
            'app_password' => ['required', 'string'],
        ]);

        $nombre = $datos['owncloud_username'];

        $url = rtrim(config('services.owncloud.url'), '/')
            . '/remote.php/dav/files/'
            . rawurlencode($nombre) . '/';

        try {
            $respuesta = Http::timeout(15)
                ->connectTimeout(5)
                ->withBasicAuth($nombre, $datos['app_password'])
                ->withoutRedirecting()
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', $url);

        } catch (ConnectionException $e) {
            return back()->withErrors([
                'owncloud' => 'No se pudo conectar con ownCloud.',
            ]);
        }

        if ($respuesta->status() !== 207) {
            return back()->withErrors([
                'owncloud' => 'No se pudo verificar la cuenta de ownCloud.',
            ]);
        }

        $usuario->owncloud_username = $nombre;
        $usuario->owncloud_app_password = $datos['app_password'];
        $usuario->save();

        return redirect()->route('panel')
            ->with('success', 'Cuenta de ownCloud vinculada correctamente.');
    }
}
