<?php
// src/suppliers/create.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../../includes/db_connection.php';

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
    if (empty($name)) $errors[] = 'Supplier name is required';

    // Insert supplier
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (name, contact_person, email, phone, address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $contact_person, $email, $phone, $address]);
        $success = true;
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Add New Supplier</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Supplier created successfully!</div>
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
            <label>Supplier Name *</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Contact Person</label>
            <input type="text" name="contact_person" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Supplier</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>