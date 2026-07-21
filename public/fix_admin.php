<?php
require_once __DIR__ . '/../includes/db_connection.php';

// Delete existing admin
$pdo->exec("DELETE FROM users WHERE username = 'admin'");

// Create fresh admin with hash generated RIGHT NOW on this server
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
$stmt->execute(['admin', $hash, 'Administrator', 'admin']);

// Test immediately
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute(['admin']);
$user = $stmt->fetch();

$verify = password_verify('admin123', $user['password_hash']);

echo "<h2>Admin User Fix</h2>";
echo "New hash created: " . $hash . "<br>";
echo "Hash length: " . strlen($hash) . "<br>";
echo "Verify 'admin123': " . ($verify ? '✅ TRUE' : '❌ FALSE') . "<br>";

if ($verify) {
    echo "<h3 style='color:green;'>✅ Login should now work!</h3>";
    echo "<a href='/'>Go to Login</a>";
} else {
    echo "<h3 style='color:red;'>❌ Still failing - PHP version issue</h3>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
}
