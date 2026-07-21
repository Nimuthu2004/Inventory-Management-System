<?php
// src/users/edit.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/db_connection.php';

$id = $_GET['id'] ?? 0;

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if (empty($full_name)) $errors[] = 'Full name is required';

    if (empty($errors)) {
        // Update with or without password change
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, role = ?, password_hash = ?
                    WHERE id = ?
                ");
                $stmt->execute([$full_name, $role, $password_hash, $id]);
                $success = true;
            }
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET full_name = ?, role = ?
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $role, $id]);
            $success = true;
        }
    }

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Edit User</h1>
    <p class="subtitle">Update user information</p>

    <?php if ($success): ?>
        <div class="alert alert-success">User updated successfully!</div>
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
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background:#f5f5f5;">
            <small style="color:#666;">Username cannot be changed</small>
        </div>

        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name']) ?>">
        </div>

        <div class="form-group">
            <label>Password (leave blank to keep current)</label>
            <input type="password" name="password" minlength="6">
            <small style="color:#666;">Minimum 6 characters if changing</small>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user" <?= (($_POST['role'] ?? $user['role']) === 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (($_POST['role'] ?? $user['role']) === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>