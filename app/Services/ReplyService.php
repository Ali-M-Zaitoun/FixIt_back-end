<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\ReplyDAO;
use App\Events\NotificationRequested;
use App\Models\Reply;
use Illuminate\Support\Facades\DB;

class ReplyService
{
    protected $replyDAO, $complaintDAO, $fileService, $cacheManager;

    public function __construct(
        ReplyDAO $replyDAO,
        ComplaintDAO $complaintDAO,
        FileManagerService $fileService,
        CacheManagerService $cacheManager
    ) {
        $this->replyDAO = $replyDAO;
        $this->complaintDAO = $complaintDAO;
        $this->fileService = $fileService;
        $this->cacheManager = $cacheManager;
    }

    public function addReply($complaint, $sender, $data): Reply
    {
        return DB::transaction(function () use ($complaint, $sender, $data) {
            $reply = $this->replyDAO->addReply($complaint->id, $sender, $data['content']);
            if (!empty($data['media'])) {
                $this->storeReplyMedia($complaint, $reply, $data['media']);
            }
            event(new NotificationRequested($sender->user, __('messages.reply_received'), $data['content']));
            return $reply;
        });
    }

    # Helper function
    private function storeReplyMedia($complaint, $reply, $media): void
    {
        $path = sprintf(
            'complaints/%s/%s/%s/%s/replies',
            $complaint->created_at->format('Y/m/d'),
            $complaint->ministry->abbreviation ?? 'unknown',
            $complaint->governorate->code ?? 'unknown',
            $complaint->reference_number
        );

        $this->fileService->storeFile(
            $reply,
            $media,
            $path,
            relationName: 'media',
            typeResolver: fn($file) => $this->fileService->detectFileType($file)
        );

        $this->cacheManager->clearComplaintCache(single: $complaint->id);
    }

    public function readReplies($complaint)
    {
        return $this->cacheManager->getReplies(
            $complaint->id,
            fn() => $this->replyDAO->readReplies($complaint)
        );
    }

    public function readOne($id)
    {
        $reply = $this->replyDAO->readOne($id);
        return $reply ? $reply : false;
    }

    public function delete($reply)
    {
        $this->cacheManager->clearComplaintCache(single: $reply->complaint_id);
        return $this->replyDAO->delete($reply);
    }
}
