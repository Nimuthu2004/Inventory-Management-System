<?php

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
