<?php

use App\Http\Middleware\CheckJabatan;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jabatan' => CheckJabatan::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('nasabah*')) {
                return route('login.show', ['role' => 'nasabah']);
            }

            return route('login.show', ['role' => 'petugas']);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
