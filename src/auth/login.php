<?php
// src/auth/login.php
// This file redirects to the main login page
// The actual login is handled by public/index.php

// If user is already logged in, redirect to dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/');
    exit();
}

// Otherwise redirect to login page
header('Location: /');
exit();
