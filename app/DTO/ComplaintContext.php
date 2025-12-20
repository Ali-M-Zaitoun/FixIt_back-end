<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class ComplaintContext
{
    public function __construct(
        public string $ministryAbbr,
        public ?string $governorateCode,
        public ?int $governorateId,
        public Collection $employees
    ) {}
}
