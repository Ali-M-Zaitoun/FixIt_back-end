<?php

namespace App\Exceptions;

class MinistryRequiresBranchException extends BusinessException
{
    protected string $messageKey = 'ministry_requires_branch';
    protected int $status = 422;
}
