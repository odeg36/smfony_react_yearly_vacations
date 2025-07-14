<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Employee;
use App\Exception\VacationCalculationException;
use DateTimeImmutable;

class VacationCalculatorService
{
    public function calculateVacationDays(Employee $employee, int $year): int
    {
        $dob = $employee->getDateOfBirth();
        $contract = $employee->getContract();
        $contractStart = $contract->getStartDate();

        $yearStart = new DateTimeImmutable("{$year}-01-01");
        $yearEnd = new DateTimeImmutable("{$year}-12-31");

        // Validate age >= 18 at year start
        $ageAtYearStart = (int)$dob->diff($yearStart)->y;
        if ($ageAtYearStart < 18) {
            throw new VacationCalculationException("Employee {$employee->getName()} is under 18 at the start of year {$year}");
        }

        // If contract starts after year ends or ends before year starts, no vacation
        if ($contractStart > $yearEnd) {
            return 0;
        }

        $employmentStartForYear = $contractStart < $yearStart ? $yearStart : $contractStart;

        $monthsEmployed = $this->fullMonthsBetween($employmentStartForYear, $yearEnd);

        if ($monthsEmployed === 0) {
            return 0;
        }

        $minimumVacationDays = $employee->getSpecialMinVacDays() ?? 26;
        $proratedDays = (int) floor($minimumVacationDays * ($monthsEmployed / 12));

        $yearsOfEmployment = (int) $contractStart->diff($yearEnd)->y;
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
