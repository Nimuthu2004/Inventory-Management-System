<?php
// public/index.php - Login Page

// Set secure session parameters - MUST be before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);

// Now start the session
session_start();

require_once __DIR__ . '/../includes/db_connection.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Rate limiting (simple implementation)
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
        if (time() - $_SESSION['last_attempt'] < 300) { // 5 minutes lockout
            $error = 'Too many login attempts. Please try again in ' .
                ceil((300 - (time() - $_SESSION['last_attempt'])) / 60) . ' minutes.';
        } else {
            // Reset attempts after lockout period
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);
        }
    }

    if (empty($error)) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Reset login attempts
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);

            // Redirect to dashboard with proper path
            header('Location: /src/dashboard/');
            exit();
        } else {
            // Failed login
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt'] = time();
            $error = 'Invalid username or password';

            // Log failed attempt
            error_log("Failed login attempt for username: $username from IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /src/dashboard/');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div class="login-container">
        <h2>Inventory Management System</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
    </div>
</body>

</html>