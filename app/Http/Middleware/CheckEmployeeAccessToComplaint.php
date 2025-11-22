<?php

namespace App\Http\Middleware;

use App\Services\ComplaintService;
use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmployeeAccessToComplaint
{
    use ResponseTrait;
    public function handle(Request $request, Closure $next): Response
    {
        $complaint = app(ComplaintService::class)->readOne($request->route('id'));
        $employee = Auth::user()->employee;

        if (!$employee->canAccessComplaint($complaint)) {
            return $this->errorResponse(__('messages.access_denied'), 403);
        }

        return $next($request);
    }
}
