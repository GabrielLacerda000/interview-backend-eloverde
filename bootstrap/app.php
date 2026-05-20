<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . "/../routes/api.php",
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if (!$request->is("api/*")) {
                return null;
            }

            // 422 - validação
            if ($e instanceof ValidationException) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "data" => null,
                        "errors" => $e->errors(),
                    ],
                    422,
                );
            }

            // 404, 403, 401
            if ($e instanceof HttpExceptionInterface) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $e->getMessage() ?: "HTTP error",
                        "data" => null,
                    ],
                    $e->getStatusCode(),
                );
            }

            // 500
            return response()->json(
                [
                    "success" => false,
                    "message" =>  $e->getMessage() ?: "Server error",
                    "data" => null,
                ],
                500,
            );
        });
    })
    ->create();
