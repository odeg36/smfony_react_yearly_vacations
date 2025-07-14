<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\EmployeeFactory;
use App\Service\VacationCalculatorService;
use App\Exception\VacationCalculationException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:calculate-vacation',
    description: 'Calculates vacation days for employees for a given year',
)]
class CalculateVacationCommand extends Command
{
    public function __construct(
        private VacationCalculatorService $vacationCalcService,
        private EmployeeFactory $employeeFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('year', InputArgument::REQUIRED, 'Year to calculate vacation days for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $year = (int) $input->getArgument('year');
            $employees = $this->employeeFactory->createFromJsonFile(__DIR__.'/../../assets/employees.json');
            foreach ($employees as $employee) {
                $vacationDays = $this->vacationCalcService->calculateVacationDays($employee, $year);
                $output->writeln(sprintf('%s: %d days', $employee->getName(), $vacationDays));
            }
        } catch (VacationCalculationException $e) {
            $output->writeln('<error>Calculation error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (Throwable $e) {
            throw new RuntimeException('Failed loading employee data: ' . $e->getMessage(), 0, $e);
        }

        return Command::SUCCESS;
    }
}
