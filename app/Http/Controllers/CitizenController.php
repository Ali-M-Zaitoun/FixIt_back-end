<?php

namespace App\Http\Controllers;

use App\Http\Resources\CitizenResource;
use App\Models\User;
use App\Services\FileManagerService;
use App\Services\SignUpService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenController extends Controller
{
    use ResponseTrait;
    public function completeInfo(Request $request, FileManagerService $service)
    {
        $request->validate([
            'national_id' => 'required|string|unique:citizens,national_id',
            'nationality' => 'required|string',
            'img'         => 'nullable|file|mimes:jpg,jpeg,png'
        ]);

        $user = User::find(Auth::user()->id);
        if (!$user) {
            return $this->errorResponse(
                __('messages.user_not_found'),
                [],
                404
            );
        }
        $user->citizen()->update([
            'national_id' => $request->input('national_id'),
            'nationality' => $request->input('nationality'),
        ]);

        if ($request->img) {
            $service->storeImg($request->img, $user->citizen);
        }

        return $this->successResponse(
            new CitizenResource($user->citizen),
            __('messages.citizen_updated'),
        );
    }
}
