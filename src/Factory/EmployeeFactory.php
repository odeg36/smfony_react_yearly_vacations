<?php

declare(strict_types=1);

namespace App\Factory;

use DateTimeImmutable;
use RuntimeException;
use App\DTO\Contract;
use App\DTO\Employee;

class EmployeeFactory
{
    /**
     * @param string $jsonFile Path to the JSON file
     *
     * @return Employee[]
     */
    public function createFromJsonFile(string $jsonFile): array
    {
        if (!file_exists($jsonFile)) {
            throw new RuntimeException("File not found: {$jsonFile}");
        }

        $jsonData = file_get_contents($jsonFile);
        if (false === $jsonData) {
            throw new RuntimeException("Cannot read file: {$jsonFile}");
        }

        $employeesArray = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        $employees = [];
        foreach ($employeesArray as $empData) {
            $employees[] = new Employee(
                $empData['name'],
                new DateTimeImmutable($empData['dateOfBirth']),
                new Contract(new DateTimeImmutable($empData['contractStartDate'])),
                $empData['specialMinimumVacationDays'] ?? null
            );
        }

        return $employees;
    }
}
