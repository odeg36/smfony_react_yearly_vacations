<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use App\DTO\Employee;
use App\Exception\VacationCalculationException;

class VacationCalculatorService
{
    public function calculateVacationDays(Employee $employee, int $year): int
    {
        // Base minimum
        $minimumVacationDays = $employee->getSpecialMinVacDays() ?? 26;

        $dob = $employee->getDateOfBirth();
        $contractStart = $employee->getContract()->getStartDate();
        $yearStart = new DateTimeImmutable("{$year}-01-01");

        if ($contractStart > new DateTimeImmutable("{$year}-12-31")) {
            // If contract starts after the year = 0 vacation days
            return 0;
        }

        // Calculate age at the start of the year
        $ageAtYearStart = (int) $dob->diff($yearStart)->y;

        if ($ageAtYearStart < 18) {
            throw new VacationCalculationException(sprintf(
                'Employee %s is under 18 years old (%d) at the start of year %d.',
                $employee->getName(),
                $ageAtYearStart,
                $year
            ));
        }

        // Calculate years of employment as of the year
        $yearsOfEmployment = 0;
        if ($contractStart->format('Y') <= $year) {
            $employmentStart = max($contractStart, $yearStart);
            $employmentEnd = new DateTimeImmutable("{$year}-12-31");
            $monthsEmployed = $this->fullMonthsBetween($employmentStart, $employmentEnd);

            // Add prorated vacation days for contract starting during the year
            $minimumVacationDays = (int) floor($minimumVacationDays * ($monthsEmployed / 12));

            // Calculate total years of employment for extra vacation days (consider contract start year)
            $yearsOfEmployment = (int) $contractStart->diff(new DateTimeImmutable("{$year}-12-31"))->y;
        }

        // Add one additional vacation day every 5 years of employment if age >= 30
        $additionalDays = 0;
        if ($ageAtYearStart >= 30) {
            $additionalDays = intdiv($yearsOfEmployment, 5);
        }

        return $minimumVacationDays + $additionalDays;
    }

    /**
     * Count full months between two dates (inclusive of the start month if start day is 1 or 15).
     */
    private function fullMonthsBetween(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        $months = 0;

        $startDay = (int) $start->format('d');
        $startMonth = (int) $start->format('m');
        $startYear = (int) $start->format('Y');

        $endMonth = (int) $end->format('m');
        $endYear = (int) $end->format('Y');

        // Calculate month difference
        $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;

        // Adjust if start day is not 1 or 15 (assuming contract can start only 1 or 15)
        // If contract starts after 15th, count from next month
        if (1 !== $startDay && 15 !== $startDay) {
            --$months;
        }

        if ($months < 0) {
            $months = 0;
        }

        return $months;
    }
}
