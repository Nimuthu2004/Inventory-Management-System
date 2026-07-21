<?php

/**
 * Database Configuration
 * Uses environment variables for sensitive data
 */

// Load environment variables if .env file exists
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
            // Remove quotes if present
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

return [
    'host' => getenv('PGHOST'),          
    'port' => getenv('PGPORT'),          // Railway auto-provides this
    'database' => getenv('PGDATABASE'),  
    'username' => getenv('PGUSER'),      
    'password' => getenv('PGPASSWORD'),  
    'driver' => 'pgsql'
];
