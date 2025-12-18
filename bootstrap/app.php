<?php

use App\Exceptions\AccessDeniedException;
use App\Http\Middleware\CheckAccessToComplaint;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SetLocaleFromHeader::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active.user' => CheckUserActive::class,
            'check.access' => CheckAccessToComplaint::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            AccessDeniedException $e,
        ) {
            if ($e->getStatusCode() === 403) {
                return response()->json([
                    'status'  => __('messages.error'),
                    'message' => __('messages.unauthorized'),
                    'errors'  => []
                ], 403);
            }
        });
    })
    ->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])
    ->create();
