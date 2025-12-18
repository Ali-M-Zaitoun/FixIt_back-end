<?php

namespace App\Exceptions;

class BranchMismatchException extends BusinessException
{
    protected string $messageKey = 'branch_mismatch';
    protected int $status = 403;
}
