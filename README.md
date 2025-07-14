# Vacation Calculator CLI Tool

## Description

This CLI tool calculates yearly vacation days for employees based on their contract details, date of birth, and special contract conditions.

---

## Setup

**Requirements:** Docker and Docker Compose installed.

1. Clone the repository and navigate to its root directory.  
2. Build and start the container:  
   `docker-compose build`  
   `docker-compose run --rm app composer install`
3. Run the application:  
   `docker compose run --rm app php bin/console app:calculate-vacation 2025`

---

## Usage

Calculate vacation days for a given year:  
`php bin/console app:calculate-vacation <year>`

**Example:**  
`php bin/console app:calculate-vacation 2025`

---

## Running Tests

`docker compose run --rm app composer test`

---

## Code Quality Tools

- Code Style Fixer:  
  ``docker compose run --rm app composer psalm-alter`
  ``docker compose run --rm app composer fix`
- Code Style Analyzer:  
  ``docker compose run --rm app composer psalm-check`
  ``docker compose run --rm app composer stan`
  ``docker compose run --rm app composer md`

---

## Assumptions & Clarifications

- Contract start dates on the 1st or 15th count as full months for proration.  
- Contracts may have an optional end date, restricting the employment period.  
- Employees under 18 years old at the start of the year are skipped with a warning.  
- Special contract minimum vacation days override the base minimum before proration.  
- Additional vacation days are granted for employees aged 30+ as one day every 5 full years employed as of Dec 31 of the given year.  
- Output lists employees in the order they appear in the input JSON.  
- If `assets/employees.json` is missing or malformed, a clear error will be displayed.

---

## Adding New Employees

Add new employees to `assets/employees.json` using the following format:
``` json
[
  {
    "name": "John Doe",
    "dateOfBirth": "1985-05-10",
    "contractStartDate": "2020-01-01",
    "contractEndDate": null,
    "specialMinimumVacationDays": 30
  }
]
```
---

## Project Structure Notes

Architecture & Design Patterns

- **DTOs (Data Transfer Objects)**: The `Employee` and `Contract` classes encapsulate input data in a strongly typed way. This promotes validation and ensures each part of the application communicates with typed objects instead of raw arrays.

- **Factory Pattern**: `EmployeeFactory` reads a JSON file and transforms each entry into an `Employee` DTO. This decouples raw data handling from application logic and centralizes transformation.

- **Service Layer**: `VacationCalculatorService` is responsible for computing the number of vacation days based on the business rules. It receives DTOs and returns values, keeping it pure and easily testable.

- **Interface Abstraction**: Both the calculator (`VacationCalculatorInterface`) and the factory (`EmployeeFactoryInterface`) are designed with interfaces to improve testability and flexibility. This allows for mocking and better dependency injection.

- **Command Layer**: The `CalculateVacationCommand` class handles the CLI interaction. It uses the service and factory to read input, execute business logic, and display the result.

- **Error Handling**: `EmployeeFactory` throws `RuntimeException`s for invalid JSON or missing files. The command catches these exceptions and displays friendly error messages.

This architecture ensures a clean separation of concerns, high testability, and ease of maintenance.
