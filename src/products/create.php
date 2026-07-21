<?php
// src/products/create.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $reorder_level = $_POST['reorder_level'] ?? 10;
    $unit_price = $_POST['unit_price'] ?? 0;

    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($sku)) $errors[] = 'SKU is required';
    if (empty($category_id)) $errors[] = 'Category is required';

    // Check if SKU exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            $errors[] = 'SKU already exists';
        }
    }

    // Insert product
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, sku, category_id, supplier_id, 
                                quantity, reorder_level, unit_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $description,
            $sku,
            $category_id,
            $supplier_id ?: null,
            $quantity,
            $reorder_level,
            $unit_price
        ]);
        $success = true;
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Add New Product</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Product created successfully!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="product-form">
        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>SKU *</label>
            <input type="text" name="sku" required value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id">
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>" <?= ($_POST['supplier_id'] ?? '') == $sup['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sup['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? 0) ?>" min="0">
            </div>

            <div class="form-group">
                <label>Reorder Level</label>
                <input type="number" name="reorder_level" value="<?= htmlspecialchars($_POST['reorder_level'] ?? 10) ?>" min="0">
            </div>

            <div class="form-group">
                <label>Unit Price</label>
                <input type="number" step="0.01" name="unit_price" value="<?= htmlspecialchars($_POST['unit_price'] ?? 0) ?>" min="0">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Product</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>