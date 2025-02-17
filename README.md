# Auto XY Challenge API

This project provides a RESTful API for managing a car catalog, built with Symfony and using MySQL as the database. The entire development environment is managed using Docker.

## Prerequisites

- Docker and Docker Compose installed on your system.

## Installation

1. Clone the repository:

```bash
git clone [https://github.com/your-username/auto_xy_challenge.git]
cd auto_xy_challenge
```

2. Configure environment variables:

Create a .env.local file from the existing .env example and modify the DATABASE_URL to point to the MySQL container:

```bash
DATABASE_URL="mysql://your-username:your-password@auto_xy_db:3306/car_catalog?serverVersion=9.2.0&charset=utf8mb4"
```

3. Start the Docker containers:

```bash
docker-compose up -d --build
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

The API will be available at http://localhost:8080/api/.

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

Wip...
