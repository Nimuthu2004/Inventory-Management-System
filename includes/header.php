<?php

/**
 * Page Header
 * Include this at the top of every page
 */

// Include config if not already included
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <!-- Use absolute path from root -->
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php include_once __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>

    <div class="container">