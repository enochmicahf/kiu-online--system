<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'KIU Support'; ?></title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Professional Dark Theme CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-brand-area">
            <span class="sidebar-label">Admin Panel</span>
            <div class="logo-box">
                <div class="logo-text">VJ<span>Pr</span>one</div>
            </div>
        </div>

        <ul class="nav-links">
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            </li>

            <li class="<?php echo ($current_page == 'complaints') ? 'active' : ''; ?>">
                <a href="dashboard.php#complaints-table"><i class="fas fa-list-ul"></i> Complaints</a>
            </li>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                    <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
                </li>
            <?php endif; ?>

            <li class="<?php echo ($current_page == 'notifications') ? 'active' : ''; ?>">
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>

            <li class="<?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            </li>

            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn btn-dark-outline w-100 text-center py-2">Logout</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div id="main-content">
        <div class="container-fluid px-0">
    <?php else: ?>
        <div class="container py-5">
    <?php endif; ?>
