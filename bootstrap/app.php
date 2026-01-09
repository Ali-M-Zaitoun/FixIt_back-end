<?php

use App\Exceptions\AccessDeniedException;
use App\Exceptions\BusinessException;
use App\Http\Middleware\CheckAccessToComplaint;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\SetLocaleFromHeader;
use App\Http\Middleware\TraceMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SetLocaleFromHeader::class);
        $middleware->append(TraceMiddleware::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active.user' => CheckUserActive::class,
            'check.access' => CheckAccessToComplaint::class,
            'cors'         => SubstituteBindings::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $getTraceId = fn() => app()->bound('trace_id') ? app('trace_id') : null;

        $exceptions->render(function (BusinessException $e) use ($getTraceId) {
            return response()->json([
                'status'   => false,
                'message'  => __("messages.{$e->messageKey()}"),
                'trace_id' => $getTraceId(),
                'errors'   => []
            ], $e->status());
        });

        $exceptions->render(function (AuthorizationException $e) use ($getTraceId) {
            return response()->json([
                'status'   => false,
                'message'  => __('messages.unauthorized'),
                'trace_id' => $getTraceId(),
                'errors'   => []
            ], 403);
        });

        $exceptions->render(function (ValidationException $e) use ($getTraceId) {
            return response()->json([
                'status'   => false,
                'message'  => __('messages.validation_error'),
                'errors'   => $e->errors(),
                'trace_id' => $getTraceId()
            ], 422);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) use ($getTraceId) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'   => false,
                    'message'  => __('messages.model_not_found'),
                    'trace_id' => $getTraceId(),
                    'errors'   => []
                ], 404);
            }
        });

        $exceptions->render(function (QueryException $e) use ($getTraceId) {
            return response()->json([
                'status'    => false,
                'message'   => __('messages.query_exception', ['trace_id' => $getTraceId()]),
                'debug'     => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ] : null,
                'trace_id'  => $getTraceId(),
            ], 500);
        });
    })
    ->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])
    ->create();
