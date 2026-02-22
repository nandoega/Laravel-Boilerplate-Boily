<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global: force JSON on every request
        $middleware->prepend(ForceJsonResponse::class);

        // Global: security headers on every response
        $middleware->append(SecurityHeaders::class);

        // Global: sanitize all string inputs (strip_tags)
        $middleware->append(SanitizeInput::class);

        // API middleware group
        $middleware->api(prepend: [
            // Ensuring pure stateless API Token auth. Removing Stateful SPA middleware.
        ]);

        // Rate limiters are registered in AppServiceProvider
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Always render exceptions as JSON for API context
        $exceptions->render(function (\Throwable $e, Request $request) {

            if ($request->expectsJson() || $request->is('api/*')) {

                // Validation errors → 422
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'meta'   => ['success' => false, 'message' => 'Validation failed'],
                        'errors' => $e->errors(),
                    ], 422);
                }

                // Auth → 401
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => 'Unauthenticated'],
                    ], 401);
                }

                // Authorization → 403
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => 'Forbidden'],
                    ], 403);
                }

                // Model not found → 404
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => 'Resource not found'],
                    ], 404);
                }

                // Route not found → 404
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => 'Endpoint not found'],
                    ], 404);
                }

                // Rate limit → 429
                if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => 'Too many requests. Slow down.'],
                    ], 429);
                }

                // Generic HTTP exception
                if ($e instanceof HttpException) {
                    return response()->json([
                        'meta' => ['success' => false, 'message' => $e->getMessage() ?: 'HTTP error'],
                    ], $e->getStatusCode());
                }

                // Catch-all: hide details in production
                $message = app()->environment('production')
                    ? 'Internal server error'
                    : $e->getMessage();

                return response()->json([
                    'meta' => ['success' => false, 'message' => $message],
                ], 500);
            }

            return null; // Let Laravel handle non-API contexts
        });
    })->create();
