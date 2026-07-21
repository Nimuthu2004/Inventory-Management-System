<?php
// src/users/index.php
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin(); // Only admins can access
require_once __DIR__ . '/../../includes/db_connection.php';

// Get all users
$users = $pdo->query("
    SELECT id, username, full_name, role, created_at 
    FROM users 
    ORDER BY created_at DESC
")->fetchAll();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $user_id = $_POST['user_id'] ?? 0;

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: index.php?deleted=1');
        exit();
    }
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="users-page">
    <h1>User Management</h1>
    <p class="subtitle">Manage system users (Admin only)</p>

    <div class="actions-bar">
        <a href="create.php" class="btn btn-primary">Add New User</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">User deleted successfully!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="products-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td>
                        <span class="badge <?= $user['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                            <?= $user['role'] ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $user['id'] ?>" class="btn-sm">Edit</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="delete_user" class="btn-sm btn-danger" style="border:none;cursor:pointer;">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="btn-sm" style="color:#999;">Current</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>