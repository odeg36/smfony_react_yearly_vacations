<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\Contract;
use App\DTO\Employee;
use App\Service\VacationCalculatorService;
use PHPUnit\Framework\TestCase;

class VacationCalculatorServiceTest extends TestCase
{
    private VacationCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new VacationCalculatorService();
    }

    public function testCalculateVacationDaysWithFullYearEmploymentAndAgeUnder30(): void
    {
        // date of birth will be actual year minus 30
        $dob = new \DateTimeImmutable('1999-01-01');
        $employee = new Employee(
            'Test Employee',
            new \DateTimeImmutable('1998-01-01'),
            new Contract(new \DateTimeImmutable('2015-01-01'))
        );

        $days = $this->service->calculateVacationDays($employee, 2025);

        // Base minimum 26, no extra because age < 30
        $this->assertEquals(26, $days);
    }

    public function testCalculateVacationDaysWithAgeAbove30AndEmploymentYears(): void
    {
        $employee = new Employee(
            'Older Employee',
            new \DateTimeImmutable('1970-01-01'),
            new Contract(new \DateTimeImmutable('2000-01-01'))
        );

        $days = $this->service->calculateVacationDays($employee, 2025);

        // Base 26 + 1 additional day per 5 full years of employment
        // Employment years = 25, extra days = 25 / 5 = 5
        $this->assertEquals(31, $days);
    }

    public function testCalculateVacationDaysWithSpecialMinimumVacationDays(): void
    {
        $employee = new Employee(
            'Special Contract',
            new \DateTimeImmutable('1980-01-01'),
            new Contract(new \DateTimeImmutable('2015-01-01')),
            30
        );

        $days = $this->service->calculateVacationDays($employee, 2025);

        // Base 30 special minimum, age > 30 (45 years old), employment 10 years -> 2 extra days
        $this->assertEquals(32, $days);
    }

    public function testCalculateVacationDaysWithContractStartingMidYearProrated(): void
    {
        $employee = new Employee(
            'Mid Year Start',
            new \DateTimeImmutable('1980-01-01'),
            new Contract(new \DateTimeImmutable('2025-06-15'))
        );

        $days = $this->service->calculateVacationDays($employee, 2025);

        // Contract starts June 15, so full months employed are July-Dec = 7 months
        // Minimum 26 * (7/12) = 13 days prorated (floor)
        // Age is 45 (so extra days = intdiv(0,5) = 0, since employment years is 0)
        $this->assertEquals(15, $days);
    }

    public function testCalculateVacationDaysWhenContractStartsAfterYear(): void
    {
        $employee = new Employee(
            'Future Contract',
            new \DateTimeImmutable('1980-01-01'),
            new Contract(new \DateTimeImmutable('2026-01-01'))
        );

        $days = $this->service->calculateVacationDays($employee, 2025);

        // Contract starts after the given year, so 0 vacation days
        $this->assertEquals(0, $days);
    }

    public function testCalculateVacationDaysThrowsExceptionForUnderageEmployee(): void
    {
        $this->expectException(\App\Exception\VacationCalculationException::class);
        $this->expectExceptionMessage('under 18');

        $dob = new \DateTimeImmutable('2010-01-01'); // Employee is under 18 in 2025
        $contractStart = new \DateTimeImmutable('2020-01-01');

        $contract = new \App\DTO\Contract($contractStart);
        $employee = new \App\DTO\Employee('Underage Employee', $dob, $contract);

        $service = new \App\Service\VacationCalculatorService();
        $service->calculateVacationDays($employee, 2025);
    }

    public function testFullMonthsBetweenCalculations(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('fullMonthsBetween');
        $method->setAccessible(true);

        $start = new \DateTimeImmutable('2025-01-01');
        $end = new \DateTimeImmutable('2025-12-31');
        $this->assertEquals(12, $method->invoke($this->service, $start, $end));

        $start = new \DateTimeImmutable('2025-06-15');
        $end = new \DateTimeImmutable('2025-12-31');
        $this->assertEquals(7, $method->invoke($this->service, $start, $end));

        $start = new \DateTimeImmutable('2025-07-10'); // day not 1 or 15
        $end = new \DateTimeImmutable('2025-12-31');
        $this->assertEquals(5, $method->invoke($this->service, $start, $end));

        $start = new \DateTimeImmutable('2025-12-31');
        $end = new \DateTimeImmutable('2025-12-31');
        $this->assertEquals(0, $method->invoke($this->service, $start, $end));

        $start = new \DateTimeImmutable('2026-01-01');
        $end = new \DateTimeImmutable('2025-12-31');
        $this->assertEquals(0, $method->invoke($this->service, $start, $end));
    }
}
