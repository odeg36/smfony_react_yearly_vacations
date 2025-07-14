<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;

final class Employee
{
    public function __construct(
        public readonly string $name,
        public readonly DateTimeImmutable $dateOfBirth,
        public readonly Contract $contract,
        public readonly ?int $specialMinVacDays = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDateOfBirth(): DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function getSpecialMinVacDays(): ?int
    {
        return $this->specialMinVacDays;
    }
}
