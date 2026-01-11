<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotification implements ShouldQueue
{
    protected $firebase;

    public $timeout = 30;
    public $tries = 3;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function handle(NotificationRequested $event): void
    {
        $tokens = [];
        $tokens = $event->user->fcmTokens;

        $event->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => get_class($event),
            'data' => [
                'title'      => $event->title,
                'body'       => $event->body,
                'ref_number' => $event->refNum,
                'params'     => $event->data
            ],
            'read_at' => null,
        ]);

        foreach ($tokens as $token) {
            $translatedTitle = __('messages.' . $event->title);
            $translatedBody  = __(
                'messages.' . $event->body,
                ['id' => $event->refNum],
            );

            $this->firebase->sendToToken($token->token, $translatedTitle, $translatedBody, $event->data);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("FCM Job Failed: " . $exception->getMessage());
    }
}
