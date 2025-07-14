# Install and commands

## Setup with Docker

1. Build the Docker image:

```bash
docker-compose up -d --build
```
2. composer install for the vendor
```bash
# perhaps will be pretty slow or even stopped mid-way, if is the case, please try running locally: composer install
docker-compose run --rm --entrypoint composer app install
```

## Run calculation command
```bash
docker-compose run --rm app app:calculate-vacation 2025
```

## Test and best practices commands
```bash
# I tried to cover all escenarios I could think of 
docker-compose run --rm --entrypoint php app vendor/bin/phpunit tests

# I included php-cs-fixer, php stan and phpmd to check that best practices are used and validated
docker-compose run --rm --entrypoint php app vendor/bin/php-cs-fixer fix --allow-risky=yes
docker-compose run --rm --entrypoint php app vendor/bin/phpstan analyse src
docker-compose run --rm --entrypoint php app vendor/bin/phpmd src json cleancode,codesize,naming
```

## The employee list is stored in assets/employees.json:
```json
[
  {
    "name": "Hans Müller",
    "dateOfBirth": "1970-12-30",
    "contractStartDate": "2001-01-01"
  },
  {
    "name": "Peter Klever",
    "dateOfBirth": "1991-07-12",
    "contractStartDate": "2016-05-15",
    "specialMinimumVacationDays": 27
  }
]
```

## DTOs
- `Employee` and `Contract` are implemented as immutable DTOs in `App\DTO`.
- DTOs encapsulate business data and prevent unintended mutation.
- All input is mapped to DTOs before processing.

## Factory Pattern

- `EmployeeFactory` in `App\Factory` is responsible for loading and transforming raw JSON input into structured `Employee` and `Contract` DTOs.
- This separates file I/O and parsing logic from business logic.
- Promotes single responsibility and enables easy test injection of employee lists.



## Assumptions:
* Employees aged 30+ earn +1 day every full 5 years of employment
* Employees starting during the year receive vacation prorated by full months worked. Only contracts starting on the 1st or 15th of a month count full months.
* I added an extra validation for underage employees. (just for adding an extra validation)
* Employees are loaded from a local JSON file, not from a database
* Docker support is included but optional.

## Ambiguities:
* As the contracts may start on 1th or 15th of the month, is not clear if the prorated calculation for a month count as full even if the employee starts on 15th.
* There is no definition of contract end date was made, the default value was left as {year}-12-31 for the prorated calculation
* No exact format of data output was defined, so a plain text is displayed for each employee (perhaps a JSON response could be better or sorting the employees list)
