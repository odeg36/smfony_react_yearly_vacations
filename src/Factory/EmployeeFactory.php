<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Employee;
use App\DTO\Contract;
use App\Interface\EmployeeFactoryInterface;
use DateTimeImmutable;
use RuntimeException;

/** @psalm-suppress UnusedClass */
final class EmployeeFactory implements EmployeeFactoryInterface
{
    /**
     * @param string $filePath
     * @return Employee[]
     * @throws RuntimeException
     */
    public function createFromJsonFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Employee file not found: {$filePath}");
        }

        $json = file_get_contents($filePath);
        if ($json === false) {
            throw new RuntimeException("Failed to read employee file: {$filePath}");
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON structure in employee file.");
        }

        $employees = [];

        foreach ($data as $row) {
            $employees[] = $this->createEmployeeFromArray($row);
        }

        return $employees;
    }

    /**
     * @param array{
     *     name: string,
     *     dateOfBirth: string,
     *     contractStartDate: string,
     *     specialMinimumVacationDays?: int|string|null
     * } $data
     */
    public function createEmployeeFromArray(array $data): Employee
    {
        $specialDays = null;
        if (isset($data['specialMinimumVacationDays'])) {
            $specialDays = is_numeric($data['specialMinimumVacationDays'])
                ? (int) $data['specialMinimumVacationDays']
                : null;
        }

        return new Employee(
            $data['name'],
            new DateTimeImmutable($data['dateOfBirth']),
            new Contract(new DateTimeImmutable($data['contractStartDate'])),
            $specialDays
        );
    }
}
