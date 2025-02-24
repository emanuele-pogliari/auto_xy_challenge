# Auto XY Challenge API

This project provides a RESTful API for managing a car catalog, built with Symfony and using MySQL as the database. The entire development environment is managed using Docker.

## Prerequisites

- Docker and Docker Compose installed on your system.

## Installation

1. Clone the repository:

```bash
git clone [https://github.com/emanuele-pogliari/auto_xy_challenge.git]
cd auto_xy_challenge
```

2. Configure environment variables:

Create a .env file from the existing .env example (rename it) and modify the DATABASE_URL to point to the MySQL container:

```bash
DATABASE_URL="mysql://your-username:your-password@db:3306/car_catalog?serverVersion=9.2.0&charset=utf8mb4"
```

3. Start the Docker containers:

```bash
docker-compose -f docker-compose.yaml up --build
```

Wait for MySQL and the Symfony application to start.

4. Install PHP dependencies:

```bash
docker-compose exec app composer install
```

5. Create the database and apply migrations:

```bash
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
```

6. Run the seeders

- This command will load the initial data into the database:

```bash
docker-compose exec app php bin/console doctrine:fixtures:load
```

## Usage

### Starting and stopping the project

To start the project

```bash
docker-compose up -d
```

To stop it

```bash
docker-compose down
```

### Accessing the API

The API will be available at http://localhost:8080/api/cars.

Example to get the list of cars with Base64 authentication (default auth credential are: user: api passowrd: apipwd):

Linux/macOS

```bash
curl -u api:apipwd -X GET http://localhost:8080/api/cars
```

Windows (PowerShell)

```bash
$authHeader = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes("api:apipwd"))
Invoke-WebRequest -Uri "http://localhost:8080/api/cars" -Method Get -Headers @{Authorization="Basic $authHeader"}
```

Or you can use Postman or another API client.

## Testing

This project includes PHPUnit tests to ensure code quality and functionality.

This project includes PHPUnit tests to ensure code quality and functionality. The tests are organized into different directories based on their type:

tests/Unit: Contains unit tests that verify the behavior of individual classes and methods in isolation.

tests/Controller: Contains functional tests that verify the behavior of controllers and API endpoints.

### Installing PHPUnit

If you don't have PHPUnit installed globally, you can install it as a development dependency using Composer:

```bash
docker-compose exec app composer require --dev phpunit/phpunit
```

To run only unit tests:

```bash
docker-compose exec app./vendor/bin/phpunit tests/Unit
```

To run only controller tests:

```bash
docker-compose exec app./vendor/bin/phpunit tests/Controller
```
