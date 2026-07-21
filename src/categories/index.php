<?php
// src/categories/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

// Get all categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];

    // Check if category has products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?deleted=1');
        exit();
    } else {
        $error = "Cannot delete category: It has $count products assigned to it.";
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="categories-page">
    <h1>Categories</h1>

    <div class="actions-bar">
        <a href="create.php" class="btn btn-primary">Add New Category</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Category deleted successfully!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="products-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Products</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= htmlspecialchars($category['description'] ?? '-') ?></td>
                    <td><?= $category['product_count'] ?></td>
                    <td><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $category['id'] ?>" class="btn-sm">Edit</a>
                        <a href="?delete=<?= $category['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>