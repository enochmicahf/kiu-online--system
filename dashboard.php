<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];

$page_title = "Dashboard - KIU Support";
$current_page = 'dashboard';

// Statistics for Dashboard Cards
if ($role == 'student') {
    // Total Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ?");
    $stmt->execute([$user_id]);
    $total_complaints = $stmt->fetchColumn();

    // Pending Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pending_complaints = $stmt->fetchColumn();

    // Resolved Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status = 'resolved'");
    $stmt->execute([$user_id]);
    $resolved_complaints = $stmt->fetchColumn();

    // Fetch User's Recent Complaints
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.id
        WHERE c.student_id = ?
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $complaints = $stmt->fetchAll();

} else {
    // Staff/Admin Statistics
    // Total Complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints");
    $total_complaints = $stmt->fetchColumn();

    // Pending Complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'");
    $pending_complaints = $stmt->fetchColumn();

    // Resolved Complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'");
    $resolved_complaints = $stmt->fetchColumn();

    // Fetch All Recent Complaints
    $stmt = $pdo->query("
        SELECT c.*, cat.name as category_name, u.username as student_username
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.id
        LEFT JOIN users u ON c.student_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 15
    ");
    $complaints = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Header with Breadcrumbs/Search -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-white mb-1">Dashboard</h3>
            <p class="text-secondary small mb-0">Welcome back, <?php echo htmlspecialchars($username); ?>!</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex justify-content-end gap-2">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="profile-avatar">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" alt="User" class="rounded-circle" width="35">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-info">
                    <p class="stat-label">MY TOTAL COMPLAINTS</p>
                    <h2 class="stat-value"><?php echo $total_complaints; ?></h2>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-info">
                    <p class="stat-label">MY PENDING COMPLAINTS</p>
                    <h2 class="stat-value"><?php echo $pending_complaints; ?></h2>
                </div>
                <div class="stat-icon grey">
                    <i class="far fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-info">
                    <p class="stat-label">RESOLVED COMPLAINTS</p>
                    <h2 class="stat-value text-success"><?php echo $resolved_complaints; ?></h2>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Complaints Table -->
        <div class="col-md-8">
            <div class="card bg-dark border-secondary" id="complaints-table">
                <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-white"><i class="fas fa-list-ul me-2"></i> Recent Complaints</h5>
                    <?php if ($role == 'student'): ?>
                        <a href="submit_complaint.php" class="btn btn-primary btn-sm">Submit New</a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="text-secondary small">
                                    <th class="border-secondary px-3">Title</th>
                                    <th class="border-secondary">Category</th>
                                    <th class="border-secondary">Status</th>
                                    <th class="border-secondary">Date</th>
                                    <th class="border-secondary text-end px-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($complaints) > 0): ?>
                                    <?php foreach ($complaints as $c): ?>
                                        <tr>
                                            <td class="px-3 border-secondary text-white fw-medium">
                                                <?php echo htmlspecialchars($c['title']); ?>
                                                <?php if (isset($c['student_username'])): ?>
                                                    <br><small class="text-secondary">By: <?php echo htmlspecialchars($c['student_username']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="border-secondary text-secondary small"><?php echo htmlspecialchars($c['category_name']); ?></td>
                                            <td class="border-secondary">
                                                <span class="badge rounded-pill badge-status-<?php echo $c['status']; ?>">
                                                    <?php echo ucfirst($c['status']); ?>
                                                </span>
                                            </td>
                                            <td class="border-secondary text-secondary small">
                                                <?php echo date('M d, Y', strtotime($c['created_at'])); ?>
                                            </td>
                                            <td class="border-secondary text-end px-3">
                                                <a href="view_complaint.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-info btn-xs py-0 px-2 small border-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">No complaints found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Components -->
        <div class="col-md-4">
            <!-- System Status Card -->
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header bg-transparent border-secondary">
                    <h6 class="mb-0 text-white">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Database Connection</span>
                        <span class="badge bg-success small">Connected</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Session Status</span>
                        <span class="badge bg-primary small">Active</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Last Action</span>
                        <span class="text-white small">Just now</span>
                    </div>
                </div>
            </div>

            <!-- Announcements/Quick Tips -->
            <div class="card bg-dark border-secondary">
                <div class="card-header bg-transparent border-secondary">
                    <h6 class="mb-0 text-white">Announcements</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item bg-transparent border-secondary py-3">
                            <h6 class="text-info small fw-bold mb-1">New System Update</h6>
                            <p class="text-secondary x-small mb-0">The complaint management system has been updated with a new dark theme for better visibility.</p>
                        </li>
                        <li class="list-group-item bg-transparent border-secondary py-3">
                            <h6 class="text-warning small fw-bold mb-1">KIU Exam Support</h6>
                            <p class="text-secondary x-small mb-0">Academic complaints regarding the upcoming exams should be submitted at least 48 hours before the exam date.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
