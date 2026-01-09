<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\LogBatch;
use Symfony\Component\HttpFoundation\Response;

class TraceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = (string) Str::uuid();
        app()->instance('trace_id', $traceId);

        \Illuminate\Support\Facades\Log::withContext([
            'trace_id' => $traceId,
            'user_id'  => Auth::id() ?? 'guest'
        ]);

        LogBatch::startBatch();

        $response = $next($request);

        LogBatch::endBatch();

        $response->headers->set('X-Trace-Id', $traceId);
        return $response;
    }
}
