<?php

namespace App\Exceptions;

use DomainException;

abstract class BusinessException extends DomainException
{
    protected string $messageKey;
    protected int $status = 409;

    public function messageKey(): string
    {
        return $this->messageKey ?? "error";
    }

    public function status(): int
    {
        return $this->status;
    }
}
