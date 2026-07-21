<?php

/**
 * Database Configuration
 * Uses environment variables for sensitive data
 */

// Load environment variables if .env file exists (for local development)
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file_get_contents(__DIR__ . '/../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Get database config from environment
$host = getenv('PGHOST') ?: getenv('DB_HOST') ?: 'localhost';
$port = getenv('PGPORT') ?: getenv('DB_PORT') ?: '5432';
$database = getenv('PGDATABASE') ?: getenv('DB_NAME') ?: 'inventory_system';
$username = getenv('PGUSER') ?: getenv('DB_USER') ?: 'postgres';
$password = getenv('PGPASSWORD') ?: getenv('DB_PASSWORD') ?: '';

return [
    'host' => $host,
    'port' => $port,
    'database' => $database,
    'username' => $username,
    'password' => $password,
    'driver' => 'pgsql'
];
