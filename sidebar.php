<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="app-logo">
            <span>Income And Expense Tracker</span>
        </div>

        <div class="user-profile">
            <div class="profile-image">
                <?= substr($_SESSION['user_name'], 0, 1) ?>
            </div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <div class="nav-menu">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="expense.php" class="nav-item active">
            <i class="fas fa-minus-circle"></i>
            <span>Add Expense</span>
        </a>
        <a href="income.php" class="nav-item">
            <i class="fas fa-plus-circle"></i>
            <span>Add Income</span>
        </a>
        <a href="reports.php" class="nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        <a href="budget.php" class="nav-item">
            <i class="fas fa-bullseye"></i>
            <span>Budget Manager</span>
        </a>
    </div>

    <div class="logout-section">
        <a href="profile.php" class="logout-btn">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
$_SESSION['user_name'] = $user_data['name'];
