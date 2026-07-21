<?php
// src/suppliers/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

// Get all suppliers
$suppliers = $pdo->query("
    SELECT s.*, COUNT(p.id) as product_count 
    FROM suppliers s 
    LEFT JOIN products p ON s.id = p.supplier_id 
    GROUP BY s.id 
    ORDER BY s.name
")->fetchAll();
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="suppliers-page">
    <h1>Suppliers</h1>

    <div class="actions-bar">
        <a href="create.php" class="btn btn-primary">Add New Supplier</a>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suppliers as $supplier): ?>
                <tr>
                    <td><?= htmlspecialchars($supplier['name']) ?></td>
                    <td><?= htmlspecialchars($supplier['contact_person'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($supplier['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($supplier['phone'] ?? '-') ?></td>
                    <td><?= $supplier['product_count'] ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $supplier['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>