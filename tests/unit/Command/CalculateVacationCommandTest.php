<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CalculateVacationCommand;
use App\DTO\Contract;
use App\DTO\Employee;
use App\Factory\EmployeeFactory;
use App\Service\VacationCalculatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateVacationCommandTest extends TestCase
{
    private VacationCalculatorService&MockObject $vacationCalculatorService;
    private EmployeeFactory&MockObject $employeeFactory;

    protected function setUp(): void
    {
        $this->vacationCalculatorService = $this->createMock(VacationCalculatorService::class);
        $this->employeeFactory = $this->createMock(EmployeeFactory::class);
    }

    private function createCommand(): CalculateVacationCommand
    {
        return new CalculateVacationCommand(
            $this->vacationCalculatorService,
            $this->employeeFactory
        );
    }

    public function testExecuteWithValidYearOutputsVacationDays(): void
    {
        $year = 2025;

        $employees = [
            new Employee('Oscar', new \DateTimeImmutable('1980-01-01'), new Contract(new \DateTimeImmutable('2010-01-01')), null),
            new Employee('Andrea', new \DateTimeImmutable('1990-05-10'), new Contract(new \DateTimeImmutable('2015-05-15')), 28),
        ];

        $this->employeeFactory
            ->expects($this->once())
            ->method('createFromJsonFile')
            ->willReturn($employees);

        $this->vacationCalculatorService
            ->method('calculateVacationDays')
            ->willReturnMap([
                [$employees[0], $year, 30],
                [$employees[1], $year, 28],
            ]);

        $application = new Application();
        $application->add($this->createCommand());

        $command = $application->find('app:calculate-vacation');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'year' => (string) $year,
        ]);

        $output = $commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Oscar: 30 days', $output);
        $this->assertStringContainsString('Andrea: 28 days', $output);
    }

    public function testExecuteWithEmptyEmployeeListOutputsNothing(): void
    {
        $year = 2025;

        $this->employeeFactory
            ->expects($this->once())
            ->method('createFromJsonFile')
            ->willReturn([]);

        $application = new Application();
        $application->add($this->createCommand());

        $command = $application->find('app:calculate-vacation');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'year' => (string) $year,
        ]);

        $output = $commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertEmpty(trim($output));
    }

    public function testExecuteThrowsExceptionWhenJsonFileNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $year = 2025;

        $this->employeeFactory
            ->expects($this->once())
            ->method('createFromJsonFile')
            ->willThrowException(new \RuntimeException('File not found'));

        $application = new Application();
        $application->add($this->createCommand());

        $command = $application->find('app:calculate-vacation');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'year' => (string) $year,
        ]);
    }
}
