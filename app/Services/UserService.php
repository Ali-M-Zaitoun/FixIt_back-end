<?php

namespace App\Services;

use App\DAO\CitizenDAO;
use App\DAO\RefreshTokenDAO;
use App\DAO\UserDAO;
use App\DAO\UserOtpDAO;
use App\Events\OTPEvent;
use App\Models\User;
use App\Traits\Loggable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        protected UserDAO $userDAO,
        protected UserOtpDAO $otpDAO,
        protected RefreshTokenDAO $refreshTokenDAO,
        protected CitizenDAO $citizenDAO,
        protected CitizenService $citizenService,
        protected FileManagerService $fileManagerService,
        protected CacheManagerService $cacheManager
    ) {}

    public function signUp(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['role'] = 'citizen';
            $user = $this->userDAO->store($data);

            if (isset($data['img'])) {
                $this->uploadProfileImage($data['img'], $user);
            }

            $otp = rand(100000, 999999);
            $this->otpDAO->store($user->id, $otp, now()->addMinutes(5));

            $user->assignRole('citizen');

            $this->citizenDAO->store($user, [
                'nationality' => $data['nationality'],
                'national_id' => $data['national_id']
            ]);

            event(new OTPEvent($otp, $user->email));

            return [
                'user_id' => $user->id,
                'otp_sent' => true,
            ];
        });
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

        $user = $this->userDAO->findById(Auth::id());

        if (!$user->status) {
            return false;
        }

        $access_token = $user->createToken('auth_token', ['*']);
        $refresh_token = Str::random(64);
        $access_token->accessToken->update(['expires_at' => now()->addMinutes(15)]);

        $this->refreshTokenDAO->delete($user->id, request()->userAgent());

        $hashedToken = hash('sha256', $refresh_token);

        $this->refreshTokenDAO->store($user->id, $hashedToken, request()->header('User-Agent'));

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
        $storedToken = $this->refreshTokenDAO->findByToken($hashedToken);

        if (!$storedToken) {
            return false;
        }

        $user = $this->userDAO->findById($storedToken->user_id);
        $access_token = $user->createToken('auth_token')->plainTextToken;

        $plainRefresh  = Str::random(64);
        $hashedRefresh  = hash('sha256', $plainRefresh);
        $this->refreshTokenDAO->update($storedToken, $hashedRefresh);

        $tokens = [
            'access_token' => $access_token,
            'refresh_token' => $plainRefresh,
        ];

        return $tokens;
    }

    public function update(User $user, $data)
    {
        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['img'])) {
                $this->updateProfileImage($data['img'], $user);
            }

            $this->userDAO->update($user, $data);

            if ($user->citizen) {
                $this->cacheManager->clearCitizenProfile($user->citizen->id);
                $this->cacheManager->clearCitizens();
            }

            return $user->fresh();
        });
    }

    public function deleteProfileImage($user)
    {
        if (!$user->image) {
            return false;
        }

        return $this->fileManagerService->deleteFile($user, $user->image->id, 'image');
    }

    public function uploadProfileImage($img, $user)
    {
        if (isset($img)) {
            $this->fileManagerService->storeFile(
                $user,
                $img,
                "users/profileImages",
                'image',
                fn() => 'img'
            );
        }
        return $user;
    }

    public function updateProfileImage($img, $user)
    {
        return DB::transaction(function () use ($img, $user) {
            if ($user->image) {
                $this->fileManagerService->deleteFile($user, $user->image->id, 'image');
                $user->image()->delete();
            }

            $this->uploadProfileImage($img, $user);

            // $this->cacheManager->clearUserRelatedCache($user);

            return $user->fresh(['image']);
        });
    }
}
