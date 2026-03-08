<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'KIU Complaints'; ?></title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fixed Sidebar CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0 text-white"><i class="fas fa-graduation-cap me-2 text-warning"></i> KIU SUPPORT</h4>
        </div>

        <ul class="nav-links">
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
            </li>
            <?php if ($_SESSION['role'] == 'student'): ?>
                <li class="<?php echo ($current_page == 'submit') ? 'active' : ''; ?>">
                    <a href="submit_complaint.php"><i class="fas fa-paper-plane me-3"></i> Submit Complaint</a>
                </li>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                    <a href="admin_users.php"><i class="fas fa-users-cog me-3"></i> User Management</a>
                </li>
            <?php endif; ?>
            <li class="<?php echo ($current_page == 'notifications') ? 'active' : ''; ?>">
                <a href="notifications.php"><i class="fas fa-bell me-3"></i> Notifications</a>
            </li>
            <li class="<?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                <a href="profile.php"><i class="fas fa-user-circle me-3"></i> My Profile</a>
            </li>
            <li>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-3"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div id="main-content">
        <div class="top-navbar bg-white rounded shadow-sm border-0 d-flex justify-content-between align-items-center">
            <button type="button" id="sidebarToggle" class="btn btn-outline-primary d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold text-dark d-none d-lg-block"><?php echo $page_title; ?></h5>
            <div class="user-profile d-flex align-items-center">
                <span class="me-3 text-secondary">Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
                <i class="fas fa-user-circle fa-2x text-primary"></i>
            </div>
        </div>
        <div class="container-fluid">
    <?php else: ?>
        <div class="container mt-5">
    <?php endif; ?>
