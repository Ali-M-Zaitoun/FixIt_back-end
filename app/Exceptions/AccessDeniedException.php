<?php

namespace App\Exceptions;

class AccessDeniedException extends BusinessException
{
    protected string $messageKey = 'unauthorized';
    protected int $status = 403;
}
