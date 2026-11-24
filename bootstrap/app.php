<?php

use App\Http\Middleware\EsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'El archivo supera el tamaño máximo permitido por PHP o el servidor web en la Raspberry Pi.',
                    'errors' => [
                        'archivo' => ['El archivo supera el tamaño máximo de subida permitido por el servidor web o PHP en la Raspberry Pi.'],
                    ],
                ], 413);
            }

            return redirect()->route('panel')->withErrors([
                'archivo' => 'El archivo supera el tamaño máximo de subida permitido por el servidor web o PHP en la Raspberry Pi.',
            ]);
        });
    })->create();
