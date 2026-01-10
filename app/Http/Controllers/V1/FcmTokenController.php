<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    use ResponseTrait;
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string',
        ]);

        $request->user()->fcmTokens()->updateOrCreate(
            ['token' => $request->token],
            ['device_type' => $request->device_type]
        );

        return $this->successResponse([], __('messages.success'), 201);
    }

    public function testNotification(FirebaseNotificationService $firebase)
    {
        $user = Auth::user();
        foreach ($user->fcmTokens as $token) {
            $firebase->sendToToken($token->token, "Come on", "I think you see the notification now :)");
        }
        return "Sent Notification";
    }

    public function my_tokens()
    {
        $user = Auth::user();
        return $user->fcmTokens;
    }
}
