<?php

use App\Http\Middleware\CheckAppLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CheckAppLocale::class);
        //$middleware->appendToGroup('api', [CheckAppLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Registro no encontrado.')], 404);
                }
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof AuthorizationException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __($e->getMessage())], 403);
                }
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __($e->getMessage())], 401);
            }
        });
    })->create();
