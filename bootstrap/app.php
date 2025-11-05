<?php

use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use App\Http\Middleware\RoleMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware
            ->statefulApi()
            ->throttleApi();

        $middleware->validateCsrfTokens(except: [
            'livekit',
        ]);
    })
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api']],
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
                data_set($data, 'success', false);
                $response->setData($data);
            }

            return $response;
        });

    })->create();
