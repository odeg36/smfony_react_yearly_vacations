<?php

declare(strict_types=1);

namespace App\Interface;

use App\DTO\Employee;

interface VacationCalculatorInterface
{
    public function calculateVacationDays(Employee $employee, int $year): int;
}
