<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\ReplyDAO;
use App\Events\NotificationRequested;
use App\Models\Citizen;
use App\Models\Employee;
use App\Models\Reply;
use Illuminate\Support\Facades\DB;

class ReplyService
{
    public function __construct(
        protected ReplyDAO $replyDAO,
        protected ComplaintDAO $complaintDAO,
        protected FileManagerService $fileService,
        protected CacheManagerService $cacheManager
    ) {}

    public function addReply($complaint, $sender, $data): Reply
    {
        return DB::transaction(function () use ($complaint, $sender, $data) {
            $reply = $this->replyDAO->addReply($complaint->id, $sender, $data['content']);
            if (!empty($data['media'])) {
                $this->storeReplyMedia($complaint, $reply, $data['media']);
            }
            if ($sender instanceof Employee) {
                event(new NotificationRequested(
                    $complaint->citizen->user,
                    'reply_received',
                    $data['content']
                ));
            }
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

    public function read($complaint)
    {
        return $this->cacheManager->getReplies(
            $complaint->id,
            fn() => $this->replyDAO->read($complaint)
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
