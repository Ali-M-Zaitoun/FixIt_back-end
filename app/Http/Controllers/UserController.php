<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignUpRequest;
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

        return $this->successResponse(['token' => $result], __('messages.login_success'), 200);
    }
}
