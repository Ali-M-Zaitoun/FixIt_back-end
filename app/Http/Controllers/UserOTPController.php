<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserOTP;
use App\Services\OTPService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class UserOTPController extends Controller
{
    use ResponseTrait;

    public function verifyOTP(Request $request, OTPService $otpService)
    {
        $request->validate([
            'otp_code' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $otpCode = $request->input('otp_code');
        $user_id = $request->input('user_id');
        $status = $otpService->verifyOTP($otpCode, $user_id);

        if ($status) {
            $user = User::find($user_id);
            $user->status = true;
            $user->save();

            return $this->successResponse(__('messages.otp_verified'), 200);
        } else {
            return $this->errorResponse(__('messages.invalid_or_expired_otp'), 400);
        }
    }

    public function resendOTP(OTPService $otpService, Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user_id = $request->input('user_id');
        $data = $otpService->resendOTP($user_id);

        if ($data) {
            return $this->successResponse($data, __('messages.otp_sent'), 200);
        } else {
            return $this->errorResponse(__('messages.otp_send_failed'), 500);
        }
    }
}
