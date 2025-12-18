<?php

namespace App\Policies;

use App\Exceptions\AccessDeniedException;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewByBranch(User $user, $branchId)
    {
        $status = $user->hasRole('super_admin') || (
            $user->hasRole('employee') &&
            $user->employee->ministry_branch_id == $branchId
        );
        if (!$status)
            throw new AccessDeniedException();
        return true;
    }

    public function viewByMinistry(User $user, $ministry_id)
    {
        $status = $user->hasRole('super_admin') || (
            $user->hasRole('ministry_manager') &&
            $user->employee->ministry_id == $ministry_id
        );
        if (!$status)
            throw new AccessDeniedException();
        return true;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->citizen && $user->citizen->id === $complaint->citizen_id) {
            return true;
        }

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
