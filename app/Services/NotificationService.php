<?php

namespace App\Services;

use App\Events\NotificationRequested;
use App\Models\Complaint;

use function PHPUnit\Framework\isEmpty;

class NotificationService
{
    public function notifyEmployees($employees, string $type): void
    {
        if (!blank($employees))
            foreach ($employees as $employee) {
                event(new NotificationRequested(
                    $employee->user,
                    'complaint_received',
                    $type
                ));
            }
    }

    public function notifyCitizen(
        Complaint $complaint,
        string $status,
        string $reason
    ): void {
        $key = $status === 'resolved'
            ? 'complaint_resolved'
            : 'complaint_rejected';

        event(new NotificationRequested(
            $complaint->citizen->user,
            'complaint_status_changed',
            __("messages.$key", ['reason' => $reason])
        ));
    }
}
