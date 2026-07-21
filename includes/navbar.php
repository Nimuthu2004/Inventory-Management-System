<nav class="navbar">
    <div class="navbar-brand">
        <a href="/src/dashboard/">Inventory System</a>
    </div>

    <div class="navbar-menu">
        <a href="/src/dashboard/">Dashboard</a>
        <a href="/src/products/">Products</a>
        <a href="/src/categories/">Categories</a>
        <a href="/src/suppliers/">Suppliers</a>
        <a href="/src/transactions/stock_in.php">Stock In</a>
        <a href="/src/transactions/stock_out.php">Stock Out</a>
        <a href="/src/reports/">Reports</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/src/users/" class="admin-link"> Admin</a>
        <?php endif; ?>
    </div>

    <div class="navbar-user">
        <span><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?></span>
        <a href="/src/auth/logout.php" class="logout-btn">Logout</a>
    </div>
</nav>