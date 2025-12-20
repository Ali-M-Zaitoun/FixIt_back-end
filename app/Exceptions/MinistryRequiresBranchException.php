<?php

namespace App\Exceptions;

use Exception;

class MinistryRequiresBranchException extends Exception
{
    protected $message = 'ministry_requires_branch';
}
