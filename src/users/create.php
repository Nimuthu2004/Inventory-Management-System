<?php
// src/users/create.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/db_connection.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Validation
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if (empty($full_name)) $errors[] = 'Full name is required';

    // Check if username exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already exists';
        }
    }

    // Insert user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$username, $password_hash, $full_name, $role]);
        $success = true;
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="form-page">
    <h1>Add New User</h1>
    <p class="subtitle">Create a new system user</p>

    <?php if ($success): ?>
        <div class="alert alert-success">User created successfully!</div>
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
            <label>Username *</label>
            <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required minlength="6">
            <small style="color:#666;">Minimum 6 characters</small>
        </div>

        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>