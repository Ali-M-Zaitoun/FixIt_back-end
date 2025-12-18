<?php

namespace App\Exceptions;

class ComplaintLockedByOtherException extends BusinessException
{
    protected string $messageKey = 'complaint_locked_by_other';
    protected int $status = 409;
}
