<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use App\Models\Citizen;
use App\Models\Employee;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFirebaseNotification implements ShouldQueue
{
    protected $firebase;
    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function handle(NotificationRequested $event): void
    {
        $tokens = [];
        $tokens = $event->user->fcmTokens;

        foreach ($tokens as $token) {
            $this->firebase->sendToToken($token->token, $event->title, $event->body, $event->data);
        }
    }
}
