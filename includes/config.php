<?php

/**
 * Application Configuration
 * Handles base URL and path resolution
 */

// Define base paths
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');

// Base URL configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Remove 'public' from path if present
if (strpos($scriptDir, '/public') !== false) {
    $baseUrl = $protocol . $host . str_replace('/public', '', $scriptDir);
} else {
    $baseUrl = $protocol . $host . $scriptDir;
}

define('BASE_URL', $baseUrl);
define('CSS_URL', BASE_URL . '/public/css');
define('JS_URL', BASE_URL . '/public/js');
