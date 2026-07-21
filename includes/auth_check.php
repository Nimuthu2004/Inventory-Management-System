<?php

/**
 * Authentication Check Middleware
 * Include this file at the top of any page that requires login
 */

// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

// Session lifetime (from config or default 1 hour)
$sessionLifetime = getenv('SESSION_LIFETIME') ?: 3600;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * Redirects to login page if not
 */
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        // Store the intended URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /');
        exit();
    }

    // Check session timeout (30 minutes of inactivity)
    $timeout = 1800; // 30 minutes
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
        logout();
    }

    // Update last activity time
    $_SESSION['login_time'] = time();
}

/**
 * Check if user is admin
 * Redirects to login if not logged in, or shows 403 if not admin
 */
function requireAdmin()
{
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        die('<h1>Access Denied</h1><p>You do not have permission to access this page.</p>');
    }
}

/**
 * Get current user info
 * Returns array with user data or null if not logged in
 */
function getCurrentUser()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? 'user'
    ];
}

/**
 * Check if current user is admin
 */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Log out user
 */
function logout()
{
    $_SESSION = array();

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: /');
    exit();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token validation failed');
    }
    return true;
}
