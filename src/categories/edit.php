<?php
// src/categories/edit.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$id = $_GET['id'] ?? 0;

// Get category data
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Category name is required';
    }

    // Check if category already exists (excluding current)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Category already exists';
        }
    }

    // Update category
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE categories 
            SET name = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $id]);
        $success = true;

        // Refresh category data
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Edit Category</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Category updated successfully!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $category['name']) ?>">
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? $category['description']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>