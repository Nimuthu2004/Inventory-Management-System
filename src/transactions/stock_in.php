<?php
// src/transactions/stock_in.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$products = $pdo->query("SELECT id, name, sku FROM products ORDER BY name")->fetchAll();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $product_id = $_POST['product_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $notes = $_POST['notes'] ?? '';

    if (empty($product_id)) $errors[] = 'Product is required';
    if ($quantity <= 0) $errors[] = 'Quantity must be greater than 0';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update product quantity
            $stmt = $pdo->prepare("
                UPDATE products 
                SET quantity = quantity + ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $product_id]);

            // Log transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions (product_id, transaction_type, quantity, user_id, notes)
                VALUES (?, 'IN', ?, ?, ?)
            ");
            $stmt->execute([$product_id, $quantity, $_SESSION['user_id'], $notes]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Transaction failed: ' . $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Stock In (Add stock to inventory)</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Stock added successfully!</div>
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
            <label>Product *</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>">
                        <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity *</label>
            <input type="number" name="quantity" required min="1" value="1">
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Add Stock</button>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>