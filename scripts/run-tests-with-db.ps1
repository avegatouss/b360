param(
    [switch]$DownAfter
)

$ErrorActionPreference = "Stop"

Write-Host "Starting test databases..." -ForegroundColor Cyan
docker compose -f docker-compose.test.yml up -d

# MySQL (Installer + dedicated instance DB)
$env:TEST_MYSQL_HOST = "127.0.0.1"
$env:TEST_MYSQL_PORT = "3307"
$env:TEST_MYSQL_DATABASE = "b360_test_system"
$env:TEST_MYSQL_USERNAME = "root"
$env:TEST_MYSQL_PASSWORD = "rootpass"

# PostgreSQL (Installer only)
$env:TEST_PGSQL_HOST = "127.0.0.1"
$env:TEST_PGSQL_PORT = "5433"
$env:TEST_PGSQL_DATABASE = "b360_test_system"
$env:TEST_PGSQL_USERNAME = "postgres"
$env:TEST_PGSQL_PASSWORD = "postgrespass"

# System DB for ExternalDbTestCase
$env:TEST_DB_DRIVER = "mysql"
$env:TEST_DB_HOST = "127.0.0.1"
$env:TEST_DB_PORT = "3307"
$env:TEST_DB_DATABASE = "b360_test_system"
$env:TEST_DB_USERNAME = "root"
$env:TEST_DB_PASSWORD = "rootpass"

Write-Host "Running test suite..." -ForegroundColor Cyan
php artisan test

if ($DownAfter) {
    Write-Host "Stopping test databases..." -ForegroundColor Cyan
    docker compose -f docker-compose.test.yml down
}
