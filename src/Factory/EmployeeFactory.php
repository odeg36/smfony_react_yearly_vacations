<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Contract;
use App\DTO\Employee;
use DateTimeImmutable;
use RuntimeException;

class EmployeeFactory
{
    /**
     * @return Employee[]
     * @throws RuntimeException
     */
    public function createFromJsonFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Employee file not found: {$filePath}");
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $employees = [];
        foreach ($data as $item) {

            $employees[] = new Employee(
                $item['name'],
                new DateTimeImmutable($item['dateOfBirth']),
                new Contract(
                    new DateTimeImmutable($item['contractStartDate']),
                ),
                $item['specialMinimumVacationDays'] ?? null
            );
        }

        return $employees;
    }
}
