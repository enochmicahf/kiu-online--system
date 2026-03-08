<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'KIU Complaints'; ?></title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper d-flex align-items-stretch">
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>KIU Support</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                </li>
                <?php if ($_SESSION['role'] == 'student'): ?>
                    <li class="<?php echo ($current_page == 'submit') ? 'active' : ''; ?>">
                        <a href="submit_complaint.php"><i class="fas fa-paper-plane me-2"></i> Submit Complaint</a>
                    </li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                        <a href="admin_users.php"><i class="fas fa-users me-2"></i> User Management</a>
                    </li>
                <?php endif; ?>
                <li class="<?php echo ($current_page == 'notifications') ? 'active' : ''; ?>">
                    <a href="notifications.php"><i class="fas fa-bell me-2"></i> Notifications</a>
                </li>
                <li class="<?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                    <a href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a>
                </li>
                <li>
                    <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </li>
            </ul>

            <div class="sidebar-footer p-3 text-center small text-white-50">
                &copy; <?php echo date('Y'); ?> KIU Support
            </div>
        </nav>
        <?php endif; ?>

        <!-- Page Content  -->
        <div id="content">
            <?php if (isset($_SESSION['user_id'])): ?>
            <nav class="navbar navbar-expand-lg navbar-light bg-light rounded mb-4 shadow-sm">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-none">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3">Hello, <strong><?php echo $_SESSION['username']; ?></strong></span>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fa-lg"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <?php endif; ?>
            <div class="container-fluid">
