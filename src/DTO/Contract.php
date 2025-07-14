<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;

final class Contract
{
    public function __construct(
        public readonly DateTimeImmutable $startDate,
    ) {
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }
}
