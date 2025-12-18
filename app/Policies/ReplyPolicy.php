<?php

namespace App\Policies;

use App\Exceptions\AccessDeniedException;
use App\Models\Reply;
use App\Models\User;

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
}
