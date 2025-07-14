<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\VacationCalculationException;
use App\Factory\EmployeeFactory;
use App\Service\Interface\VacationCalculatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;

/** @psalm-suppress UnusedClass */
final class CalculateVacationCommand extends Command
{
    protected static $defaultName = 'app:calculate-vacation';

    public function __construct(
        private VacationCalculatorInterface $vacationCalcService,
        private EmployeeFactory $employeeFactory,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Calculate vacation days for employees for a given year')
            ->addArgument('year', InputArgument::REQUIRED, 'Year to calculate vacation days for');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $yearArg = $input->getArgument('year');

        if (!is_numeric($yearArg)) {
            throw new InvalidArgumentException('Year must be a number');
        }

        $year = (int) $yearArg;
        $employees = $this->employeeFactory->createFromJsonFile(__DIR__ . '/../../assets/employees.json');

        foreach ($employees as $employee) {
            try {
                $days = $this->vacationCalcService->calculateVacationDays($employee, $year);
                $output->writeln(sprintf('%s: %d days', $employee->getName(), $days));
            } catch (VacationCalculationException $e) {
                $output->writeln(sprintf('<comment>Skipping %s: %s</comment>', $employee->getName(), $e->getMessage()));
            }
        }
        return Command::SUCCESS;
    }
}
