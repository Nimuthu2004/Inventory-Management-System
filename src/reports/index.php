<?php
// src/reports/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$report_type = $_GET['type'] ?? 'inventory';

switch ($report_type) {
    case 'inventory':
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name, s.name as supplier_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            ORDER BY p.quantity ASC
        ");
        $data = $stmt->fetchAll();
        $title = 'Inventory Report';
        break;

    case 'transactions':
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT t.*, p.name as product_name, u.full_name as user_name 
            FROM transactions t 
            JOIN products p ON t.product_id = p.id 
            JOIN users u ON t.user_id = u.id 
            WHERE DATE(t.created_at) BETWEEN ? AND ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        $data = $stmt->fetchAll();
        $title = 'Transaction Report';
        break;
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="reports-page">
    <h1><?= $title ?></h1>

    <div class="report-controls">
        <div class="btn-group">
            <a href="?type=inventory" class="btn <?= $report_type === 'inventory' ? 'btn-primary' : 'btn-secondary' ?>">
                Inventory
            </a>
            <a href="?type=transactions" class="btn <?= $report_type === 'transactions' ? 'btn-primary' : 'btn-secondary' ?>">
                Transactions
            </a>
        </div>

        <?php if ($report_type === 'transactions'): ?>
            <form method="GET" class="date-filter">
                <input type="hidden" name="type" value="transactions">
                <label>From:</label>
                <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>">
                <label>To:</label>
                <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                <button type="submit" class="btn-sm">Filter</button>
            </form>
        <?php endif; ?>
    </div>

    <table class="report-table">
        <?php if ($report_type === 'inventory'): ?>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['sku']) ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['supplier_name'] ?? '-') ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>
                            <?php if ($item['quantity'] <= 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($item['quantity'] <= $item['reorder_level']): ?>
                                <span class="badge badge-warning">Low Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        <?php elseif ($report_type === 'transactions'): ?>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><span class="badge badge-<?= $item['transaction_type'] === 'IN' ? 'success' : 'danger' ?>">
                                <?= $item['transaction_type'] ?>
                            </span></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= htmlspecialchars($item['user_name']) ?></td>
                        <td><?= $item['created_at'] ?></td>
                        <td><?= htmlspecialchars($item['notes'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        <?php endif; ?>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>