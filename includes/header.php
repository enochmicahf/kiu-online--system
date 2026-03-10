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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <div class="sidebar-brand-area">
            <span class="sidebar-label">University Support</span>
            <h3 class="text-primary fw-bold mb-0" style="letter-spacing: -1px;">KIU</h3>
            <p class="text-white small opacity-50 mb-0">Complaint System</p>
        </div>

        <ul class="nav-links">
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="<?php echo ($current_page == 'complaints') ? 'active' : ''; ?>">
                <a href="dashboard.php#complaints-table">
                    <i class="fas fa-list-ul"></i>
                    <span>Complaints</span>
                </a>
            </li>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                    <a href="admin_users.php">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'announcements') ? 'active' : ''; ?>">
                    <a href="admin_announcements.php">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="<?php echo ($current_page == 'notifications') ? 'active' : ''; ?>">
                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li class="<?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn btn-primary w-100 py-2">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Top Navigation Bar -->
    <header class="top-nav">
        <div class="d-flex align-items-center">
            <div class="sidebar-toggle me-3 d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="search-box d-none d-md-block">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for complaints, categories...">
            </div>
        </div>

        <div class="nav-user-area">
            <div class="notifications-bell me-3">
                <a href="notifications.php" class="text-secondary">
                    <i class="fas fa-bell fs-5"></i>
                </a>
            </div>
            <div class="d-flex align-items-center bg-dark p-1 rounded-pill pe-3">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=3b82f6&color=fff"
                     alt="User" class="rounded-circle me-2" width="32">
                <span class="small fw-semibold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <i class="fas fa-chevron-down ms-2 small text-secondary"></i>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content">
        <div class="container-fluid p-0">
    <?php else: ?>
        <div class="container">
    <?php endif; ?>
