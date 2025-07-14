<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\Contract;
use App\DTO\Employee;
use App\Exception\VacationCalculationException;
use App\Service\VacationCalculatorService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class VacationCalculatorServiceTest extends TestCase
{
    private VacationCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new VacationCalculatorService();
    }

    public function testCalculateVacationDaysThrowsExceptionForUnderage(): void
    {
        $this->expectException(VacationCalculationException::class);

        $dob = new DateTimeImmutable('2010-01-01');
        $contract = new Contract(new DateTimeImmutable('2020-01-01'));

        $employee = new Employee('Underage', $dob, $contract);

        $this->service->calculateVacationDays($employee, 2025);
    }

    public function testCalculateVacationDaysWithFullYearEmployment(): void
    {
        $dob = new DateTimeImmutable('1970-01-01');
        $contract = new Contract(new DateTimeImmutable('2000-01-01'));

        $employee = new Employee('Test Employee', $dob, $contract);

        $days = $this->service->calculateVacationDays($employee, 2025);
        $this->assertGreaterThanOrEqual(26, $days);
    }

    public function testCalculateVacationDaysWithContractStartMidYear(): void
    {
        $dob = new DateTimeImmutable('1970-01-01');
        $contract = new Contract(new DateTimeImmutable('2025-07-01'));

        $employee = new Employee('Mid Year', $dob, $contract);

        $days = $this->service->calculateVacationDays($employee, 2025);

        $this->assertLessThan(26, $days);
        $this->assertGreaterThan(0, $days);
    }

    public function testCalculateVacationDaysNoEmploymentInYear(): void
    {
        $dob = new DateTimeImmutable('1970-01-01');
        $contract = new Contract(
            new DateTimeImmutable('2026-01-01')
        );

        $employee = new Employee('Future Employee', $dob, $contract);

        $days = $this->service->calculateVacationDays($employee, 2025);

        $this->assertSame(0, $days);
    }
}
