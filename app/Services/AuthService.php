<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOTP;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function signUp(array $data)
    {
        $user = User::create($data);
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        UserOTP::create([
            'user_id' => $user->id,
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
        ]);

        // Mail::to($user->email)->send(new OtpMail($otp)); // Assuming OtpMail is a Mailable class
        return [
            'user_id' => $user->id,
            'otp_sent' => true,
            'otp_code' => $otp, // For testing purposes; remove in production
        ];
    }

    public function login(array $data)
    {
        $loginType = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [
            $loginType => $data['login'],
            'password' => $data['password'],
        ];

        if (!Auth::attempt($credentials)) {
            return false;
        }

        $user = User::where('id', Auth::id())->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        return $token;
    }
}
