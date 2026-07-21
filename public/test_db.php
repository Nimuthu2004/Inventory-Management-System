<?php
// public/test_db.php
require_once '../includes/db_connection.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test the connection
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color:green;'>✅ Database connected successfully!</p>";

    // Show database info
    $stmt = $pdo->query("SELECT current_database() as db_name, version() as pg_version");
    $info = $stmt->fetch();
    echo "<p>Database: <strong>{$info['db_name']}</strong></p>";
    echo "<p>PostgreSQL Version: <strong>{$info['pg_version']}</strong></p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}
