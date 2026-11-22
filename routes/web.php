<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OwnCloudConnectionController;
use App\Http\Controllers\ArchivoController;

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


    Route::get('/conectar-owncloud',[OwnCloudConnectionController::class, 'mostrar'])->name('owncloud.conectar');

    Route::post('/conectar-owncloud',[OwnCloudConnectionController::class, 'conectar'])->middleware('throttle:5,1');

    Route::post('/archivos/subir', [ArchivoController::class, 'subir']) ->middleware('throttle:10,1')->name('archivos.subir');

    Route::get('/archivos/descargar', [ArchivoController::class, 'descargar'])->middleware('throttle:30,1')->name('archivos.descargar');

});
