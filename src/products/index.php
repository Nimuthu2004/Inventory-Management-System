<?php
// src/products/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

// Search functionality
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (p.name ILIKE ? OR p.sku ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY p.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="products-page">
    <h1>Products</h1>

    <div class="actions-bar">
        <a href="create.php" class="btn btn-primary">Add New Product</a>
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by name or SKU" value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Search</button>
        </form>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['sku']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($product['supplier_name'] ?? '-') ?></td>
                    <td class="<?= $product['quantity'] <= $product['reorder_level'] ? 'low-stock' : '' ?>">
                        <?= $product['quantity'] ?>
                        <?php if ($product['quantity'] <= $product['reorder_level']): ?>
                            <span class="badge badge-warning">Low Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>$<?= number_format($product['unit_price'], 2) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $product['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>