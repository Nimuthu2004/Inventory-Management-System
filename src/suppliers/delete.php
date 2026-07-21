<?php
// src/suppliers/delete.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$id = $_GET['id'] ?? 0;

// Check if supplier exists
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: index.php?error=notfound');
    exit();
}

try {
    // Check if supplier has associated products
    $stmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM products WHERE supplier_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();

    if ($result['product_count'] > 0) {
        // Set products with this supplier to null instead of preventing deletion
        $stmt = $pdo->prepare("UPDATE products SET supplier_id = NULL WHERE supplier_id = ?");
        $stmt->execute([$id]);
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Delete the supplier
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);

    // Log the deletion (if you have a logging system)
    // logActivity($_SESSION['user_id'], 'DELETE', 'supplier', $id);

    $pdo->commit();
    header('Location: index.php?deleted=1');
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Supplier Delete Error: " . $e->getMessage());
    header('Location: index.php?error=deletefailed');
    exit();
}
