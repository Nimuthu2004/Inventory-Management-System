<?php
// public/router.php

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Serve static files directly (CSS, JS, images)
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false;
    }
}

// Home page / Login
if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
    return;
}

// Handle /dashboard/ route
if ($path === '/src/dashboard/' || $path === '/dashboard/' || $path === '/dashboard') {
    require __DIR__ . '/../src/dashboard/index.php';
    return;
}

// Handle other src routes
if (preg_match('#^/src/(.+)$#', $path, $matches)) {
    $route = $matches[1];

    // Build the file path
    $filePath = __DIR__ . '/../src/' . $route;

    // If it ends with /, try index.php
    if (substr($route, -1) === '/') {
        $filePath .= 'index.php';
    }

    // If it's a directory, try index.php
    if (is_dir($filePath)) {
        $filePath .= '/index.php';
    }

    // Add .php extension if missing
    if (!str_ends_with($filePath, '.php')) {
        $filePath .= '.php';
    }

    // Include the file if it exists
    if (file_exists($filePath)) {
        // Change directory to the file's directory so relative includes work
        $originalDir = getcwd();
        chdir(dirname($filePath));

        require $filePath;

        // Restore original directory
        chdir($originalDir);
        return;
    }
}

// If we get here, return 404
http_response_code(404);
?>
<!DOCTYPE html>
<html>

<head>
    <title>404 - Not Found</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div style="text-align:center; padding:50px;">
        <h1>404 - Page Not Found</h1>
        <p>The page you requested was not found.</p>
        <p><a href="/" style="color:#3498db;">Go to Login Page</a></p>
        <?php if (ini_get('display_errors')): ?>
            <p style="color:#999; margin-top:20px;">
                Requested path: <?= htmlspecialchars($path) ?>
            </p>
        <?php endif; ?>
    </div>
</body>

</html>