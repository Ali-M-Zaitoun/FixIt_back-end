<?php

namespace App\Services;

use App\Http\Requests\BaseUserRequest;
use App\Models\User;
use App\Models\UserOTP;

class OTPService
{
    public function verifyOTP(string $otpCode, $user_id)
    {
        $userOtp = UserOTP::where('otp_code', $otpCode)
            ->where('user_id', $user_id)
            ->where('expires_at', '>', now())
            ->first();

        if ($userOtp) {
            $userOtp->delete();
            return true;
        }

        return false;
    }

    public function resendOTP($user_id)
    {
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        $user = User::find($user_id);

        if (!$user || $user->status) {
            return null;
        }

        UserOTP::where('user_id', $user_id)->delete();

        UserOTP::create([
            'user_id' => $user_id,
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
        ]);

        // Mail::to($user->email)->send(new OtpMail($otp)); // Assuming OtpMail is a Mailable class
        return [
            'user_id' => $user_id,
            'otp_sent' => __('messages.true'),
            'otp_code' => $otp, // For testing purposes; remove in production
        ];
    }
}
