<?php
// src/products/delete.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$id = $_GET['id'] ?? 0;

// Check if product exists
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: index.php');
    exit();
}

// Delete product
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?deleted=1');
exit();
