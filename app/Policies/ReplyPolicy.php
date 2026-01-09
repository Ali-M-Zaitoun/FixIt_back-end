<?php

namespace App\Policies;

use App\Exceptions\AccessDeniedException;
use App\Models\Complaint;
use App\Models\Reply;
use App\Models\User;

use function PHPUnit\Framework\isEmpty;

class ReplyPolicy
{
    public function viewReply(User $user, Reply $reply): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->citizen && $user->citizen->id === $reply->sender_id) {
            return true;
        }

        $complaint = $reply->complaint;

        if ($user->employee) {
            if (
                $user->hasRole('ministry_manager') &&
                $user->employee->ministry_id === $complaint->ministry_id
            ) {
                return true;
            }

            if ($user->employee->ministry_branch_id === $complaint->ministry_branch_id) {
                return true;
            }
        }
        throw new AccessDeniedException();
    }

    public function addReply(User $user, Complaint $complaint)
    {
        if ($user->citizen)
            return true;

        if ($user->employee) {
            $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
            $lockedByOther = $complaint->locked_by && $complaint->locked_by != $user->employee->id;
            if ($lockExpired)
                return true;

            if (!$lockExpired && !$lockedByOther)
                return true;
        }

        throw new AccessDeniedException();
    }
}
