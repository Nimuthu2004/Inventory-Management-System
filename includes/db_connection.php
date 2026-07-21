<?php

/**
 * Database Connection
 * Creates PDO connection to PostgreSQL database
 */

// Database configuration
$config = require_once __DIR__ . '/../config/database.php';

try {
    // Build DSN for PostgreSQL
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $config['host'],
        $config['port'],
        $config['database']
    );

    // Create PDO instance
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// In includes/db_connection.php
// Consider adding:
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    if (ini_get('display_errors')) {
        echo "Error: " . $e->getMessage();
    } else {
        include 'error.php';
    }
});
