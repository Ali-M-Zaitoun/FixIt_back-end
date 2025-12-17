<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\ReplyDAO;
use Illuminate\Support\Facades\Cache;

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

    public function addReply($id, $sender, $data)
    {
        $complaint = $this->complaintDAO->readOne($id);
        if (isset($data['media'])) {
            $media = $data['media'];
            $datePath = $complaint->created_at->format('Y/m/d');
            $ministryAbbr = $complaint->ministry->abbreviation ?? 'unknown';
            $governorateCode = $complaint->governorate->code ?? 'unknown';
            $ref = $complaint->reference_number;
            $folderPath = sprintf(
                'complaints/%s/%s/%s/%s/new',
                $datePath,
                $ministryAbbr,
                $governorateCode,
                $ref
            );

            $reply = $this->replyDAO->addReply($id, $sender, $data['content']);
            $this->fileService->storeFile(
                $reply,
                $media,
                $folderPath,
                relationName: 'media',
                typeResolver: fn($file) => $this->fileService->detectFileType($file)
            );
            $this->cacheManager->clearComplaintCache(single: $complaint->id);
            return $reply;
        }
    }

    public function readReplies($id)
    {
        $complaint = $this->complaintDAO->readOne($id);
        if (!$complaint)
            return false;

        $cacheKey = "complaint:{$id}:replies";

        return Cache::remember($cacheKey, 3600, function () use ($complaint) {
            return $this->replyDAO->readReplies($complaint);
        });
    }

    public function readOne($id)
    {
        $reply = $this->replyDAO->readOne($id);
        return $reply ? $reply : false;
    }

    public function delete($id)
    {
        $reply = $this->replyDAO->readOne($id);
        if (!$reply) {
            return false;
        }

        $this->cacheManager->clearComplaintCache(single: $reply->complaint_id);
        return $this->replyDAO->delete($reply);
    }
}
