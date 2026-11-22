<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\AdminController;

Route::view('/', 'inicio')->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrar'])
        ->middleware('throttle:5,1');

    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'ingresar'])
        ->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/panel', [ArchivoController::class, 'index'])->name('panel');
    Route::post('/salir', [AuthController::class, 'salir'])->name('salir');

    Route::post('/archivos/subir', [ArchivoController::class, 'subir'])
        ->middleware('throttle:10,1')
        ->name('archivos.subir');

    Route::get('/archivos/descargar', [ArchivoController::class, 'descargar'])
        ->middleware('throttle:30,1')
        ->name('archivos.descargar');

    Route::delete('/archivos/eliminar', [ArchivoController::class, 'eliminar'])
        ->middleware('throttle:30,1')
        ->name('archivos.eliminar');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/grupos', [AdminController::class, 'crearGrupo'])->name('grupos.crear');
    Route::post('/usuarios', [AdminController::class, 'crearUsuario'])->name('usuarios.crear');
    Route::put('/usuarios/{user}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
    Route::delete('/usuarios/{user}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
});