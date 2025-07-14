<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Employee;
use App\Interface\VacationCalculatorInterface;
use App\Exception\VacationCalculationException;
use DateTimeImmutable;

/** @psalm-suppress UnusedClass */
final class VacationCalculatorService implements VacationCalculatorInterface
{
    public function calculateVacationDays(Employee $employee, int $year): int
    {
        $dob = $employee->getDateOfBirth();
        $contract = $employee->getContract();
        $contractStart = $contract->getStartDate();

        $yearStart = new DateTimeImmutable("{$year}-01-01");
        $yearEnd = new DateTimeImmutable("{$year}-12-31");

        // Validate age >= 18 at year start
        $ageAtYearStart = $dob->diff($yearStart)->y;
        if ($ageAtYearStart < 18) {
            throw new VacationCalculationException("Employee {$employee->getName()} is under 18 at the start of year {$year}");
        }

        // If contract starts after year ends or ends before year starts, no vacation
        if ($contractStart > $yearEnd) {
            return 0;
        }

        $employmentStartYear = $contractStart < $yearStart ? $yearStart : $contractStart;

        $monthsEmployed = $this->fullMonthsBetween($employmentStartYear, $yearEnd);

        if ($monthsEmployed === 0) {
            return 0;
        }

        $minimumVacationDays = $employee->getSpecialMinVacDays() ?? 26;
        $proratedDays = (int) floor((float) $minimumVacationDays * ((float) $monthsEmployed / 12.0));

        $yearsOfEmployment = $contractStart->diff($yearEnd)->y;
        $additionalDays = 0;
        if ($ageAtYearStart >= 30) {
            $additionalDays = intdiv($yearsOfEmployment, 5);
        }

        return $proratedDays + $additionalDays;
    }

    private function fullMonthsBetween(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        $startDay = (int)$start->format('d');
        $startMonth = (int)$start->format('m');
        $startYear = (int)$start->format('Y');

        $endMonth = (int)$end->format('m');
        $endYear = (int)$end->format('Y');

        // Calculate month difference
        $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;

        if ($startDay !== 1 && $startDay !== 15) {
            $months--;
        }

        return max(0, $months);
    }
}
