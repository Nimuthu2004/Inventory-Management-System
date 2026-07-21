<?php
// src/suppliers/edit.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

$id = $_GET['id'] ?? 0;

// Get supplier data
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: index.php?error=notfound');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $name = $_POST['name'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Supplier name is required';
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Check for duplicate name (excluding current supplier)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'A supplier with this name already exists';
        }
    }

    // Update supplier
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE suppliers 
                SET name = ?, contact_person = ?, email = ?, phone = ?, address = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $contact_person, $email, $phone, $address, $id]);
            $success = true;

            // Refresh supplier data
            $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $supplier = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log("Supplier Update Error: " . $e->getMessage());
        }
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Edit Supplier</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Supplier updated successfully!</div>
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
            <label for="name">Supplier Name *</label>
            <input type="text" id="name" name="name" required
                value="<?= htmlspecialchars($_POST['name'] ?? $supplier['name']) ?>">
        </div>

        <div class="form-group">
            <label for="contact_person">Contact Person</label>
            <input type="text" id="contact_person" name="contact_person"
                value="<?= htmlspecialchars($_POST['contact_person'] ?? $supplier['contact_person']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? $supplier['email']) ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone"
                    value="<?= htmlspecialchars($_POST['phone'] ?? $supplier['phone']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? $supplier['address']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Supplier</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>