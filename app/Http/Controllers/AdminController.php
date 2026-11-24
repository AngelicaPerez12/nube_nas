<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $grupos = Grupo::withCount('usuarios')->get();
        $usuarios = User::with('grupo')->orderBy('id')->get();

        return view('admin.index', compact('grupos', 'usuarios'));
    }

    public function crearGrupo(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:grupos,nombre'],
        ]);

        $carpeta = 'grupo_'.Str::slug($datos['nombre'], '_');

        Grupo::create([
            'nombre' => $datos['nombre'],
            'carpeta' => $carpeta,
        ]);

        if (! Storage::disk('nas')->exists($carpeta)) {
            Storage::disk('nas')->makeDirectory($carpeta);
        }

        return back()->with('success', 'Grupo creado y carpeta física asignada en el NAS.');
    }

    public function crearUsuario(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:users,name'],
            'password' => ['required', 'string', 'min:5'],
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'permiso' => ['required', 'in:lectura,escritura'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $datos['name'],
            'password' => Hash::make($datos['password']),
            'grupo_id' => $datos['grupo_id'] ?? null,
            'permiso' => $datos['permiso'],
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function actualizarUsuario(Request $request, User $user)
    {
        $datos = $request->validate([
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'permiso' => ['required', 'in:lectura,escritura'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'grupo_id' => $datos['grupo_id'] ?? null,
            'permiso' => $datos['permiso'],
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return back()->with('success', "Permisos actualizados para {$user->name}.");
    }

    public function eliminarUsuario(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta de administrador.']);
        }

        $carpetaPrivada = 'privado/'.$user->name;

        if (Storage::disk('nas')->exists($carpetaPrivada)) {
            Storage::disk('nas')->deleteDirectory($carpetaPrivada);
        }

        $nombre = $user->name;
        $user->delete();

        return back()->with('success', "Usuario '{$nombre}' eliminado correctamente.");
    }
}
