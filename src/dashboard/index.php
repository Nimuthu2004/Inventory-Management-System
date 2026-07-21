<?php
// src/dashboard/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetch()['total'];

// Low stock products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE quantity <= reorder_level");
$stats['low_stock'] = $stmt->fetch()['total'];

// Recent transactions
$stmt = $pdo->query("
    SELECT t.*, p.name as product_name, u.full_name as user_name 
    FROM transactions t 
    JOIN products p ON t.product_id = p.id 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard">
    <h1>Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <p><?= $stats['total_products'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Low Stock Items</h3>
            <p><?= $stats['low_stock'] ?></p>
        </div>
    </div>

    <div class="recent-transactions">
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>User</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_transactions as $trans): ?>
                    <tr>
                        <td><?= htmlspecialchars($trans['product_name']) ?></td>
                        <td><?= $trans['transaction_type'] ?></td>
                        <td><?= $trans['quantity'] ?></td>
                        <td><?= htmlspecialchars($trans['user_name']) ?></td>
                        <td><?= $trans['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>