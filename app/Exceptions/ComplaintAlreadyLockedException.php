<?php

namespace App\Exceptions;

class ComplaintAlreadyLockedException extends BusinessException
{
    protected string $messageKey = 'complaint_already_locked';
    protected int $status = 409;
}
