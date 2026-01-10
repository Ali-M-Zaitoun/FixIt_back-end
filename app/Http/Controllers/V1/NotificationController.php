<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ResponseTrait;
    public function read()
    {
        $notifications = Auth::user()->notifications;
        return $this->successResponse(NotificationResource::collection($notifications), __('messages.success'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return $this->successResponse([], __('messages.noti_read'));
    }

    public function destroy($id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
        return $this->successResponse([], __('messages.noti_deleted'));
    }
}
