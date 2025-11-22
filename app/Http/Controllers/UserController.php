<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignUpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    use ResponseTrait;
    public function signUp(SignUpRequest $request, AuthService $authService)
    {
        $result = $authService->signUp($request->validated());
        if ($result) {
            return $this->successResponse($result, __('messages.otp_sent'), 201);
        } else {
            return $this->errorResponse(__('messages.registration_failed'), 500);
        }
    }

    public function login(Request $request, AuthService $authService)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $authService->login($request->only('login', 'password'));
        if (!$result) {
            return $this->errorResponse(__('messages.invalid_credentials'), 401);
        }
        $result['user'] = new UserResource($result['user']);
        return $this->successResponse($result, __('messages.login_success'), 200);
    }

    public function refreshToken(Request $request, AuthService $authService)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $result = $authService->refreshToken($request->input('refresh_token'));
        if (!$result) {
            return $this->errorResponse(__('messages.invalid_refresh_token'), 401);
        }

        return $this->successResponse($result, __('messages.token_refreshed'), 200);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return $this->successResponse([], __('messages.logout_success'));
    }
}
