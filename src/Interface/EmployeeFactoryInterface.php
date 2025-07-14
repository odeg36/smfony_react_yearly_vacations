<?php

declare(strict_types=1);

namespace App\Interface;

use App\DTO\Employee;
use RuntimeException;

interface EmployeeFactoryInterface
{
    /**
     * @param string $filePath
     * @return Employee[]
     * @throws RuntimeException
     */
    public function createFromJsonFile(string $filePath): array;
}
