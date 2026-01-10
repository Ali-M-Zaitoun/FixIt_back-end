<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReplyResource;
use App\Models\Complaint;
use App\Models\Reply;
use App\Services\ReplyService;
use App\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class ReplyController extends Controller
{
    use ResponseTrait, AuthorizesRequests;

    public function __construct(protected ReplyService $replyService) {}

    public function addReply(Complaint $complaint, Request $request)
    {
        $this->authorize('view', $complaint);
        $user = Auth::user();
        // $this->authorize('addReply', $complaint);
        $sender = $user->citizen ?? $user->employee;

        $request->validate([
            'content' => 'required|string|max:500',
            'media'   => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ]);

        $result = $this->replyService->addReply($complaint, $sender, $request->all());
        if ($result) {
            return $this->successResponse($result, __('messages.reply_sent'));
        }
        return $this->errorResponse(__('messages.reply_failed'));
    }

    public function read(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        $result = $this->replyService->read($complaint);
        if ($result->isEmpty())
            return $this->successResponse([], __('messages.empty'));
        return $this->successResponse(ReplyResource::collection($result), __('messages.replies_retrieved'));
    }

    public function delete(Reply $reply)
    {
        $this->authorize('viewReply', $reply);

        $reply = $this->replyService->delete($reply);
        if ($reply) {
            return $this->successResponse([], __('messages.deleted_successfully'));
        }

        return $this->errorResponse(__('messages.not_found'), 404);
    }
}
