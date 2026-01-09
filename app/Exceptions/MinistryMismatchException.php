<?php

namespace App\Exceptions;

use Exception;

class MinistryMismatchException extends BusinessException
{
    protected string $messageKey = 'ministry_branch_mismatch';
    protected int $status = 422;
}
