<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserOTP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        if ($data['role'] === 'citizen') {
            $user->assignRole('citizen');
            $user->citizen()->create();
        }

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

        if (!$user->status) {
            return false;
        }

        $access_token = $user->createToken('auth_token', ['*']);
        $refresh_token = Str::random(64);
        $access_token->accessToken->update(['expires_at' => now()->addMinutes(15)]);

        RefreshToken::where('user_id', $user->id)
            ->where('device_name', request()->userAgent())
            ->delete();

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refresh_token),
            'device_name' => request()->header('User-Agent') ?? 'unknown',
            'expires_at' => now()->addDays(30),
        ]);

        $token = [
            'access_token' => $access_token->plainTextToken,
            'refresh_token' => $refresh_token,
        ];

        $permissions = $user->roles
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->unique()
            ->values()
            ->toArray();

        $data = ['user' => $user, 'tokens' => $token, 'permissions' => $permissions];
        return $data;
    }

    public function refreshToken(string $refreshToken)
    {
        $hashedToken = hash('sha256', $refreshToken);
        $storedToken = RefreshToken::where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();

        if (!$storedToken) {
            return false;
        }

        $user = User::find($storedToken->user_id);
        $access_token = $user->createToken('auth_token')->plainTextToken;

        $new_refresh_token = Str::random(64);
        $storedToken->update([
            'token' => hash('sha256', $new_refresh_token),
            'expires_at' => now()->addDays(30),
        ]);

        $tokens = [
            'access_token' => $access_token,
            'refresh_token' => $new_refresh_token,
        ];

        return $tokens;
    }
}
