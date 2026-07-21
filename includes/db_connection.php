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
    // Show detailed error for debugging
    error_log("Database Connection Error: " . $e->getMessage());

    // Show more details
    $errorMsg = "Database connection failed.<br>";
    $errorMsg .= "Host: " . $config['host'] . "<br>";
    $errorMsg .= "Port: " . $config['port'] . "<br>";
    $errorMsg .= "Database: " . $config['database'] . "<br>";
    $errorMsg .= "Error: " . $e->getMessage();

    die($errorMsg);
}
