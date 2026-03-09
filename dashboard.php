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

    // Pending Verification (Students)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND is_verified = 0");
    $pending_verifications = $stmt->fetchColumn();

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
    <!-- Header with Welcome Message -->
    <div class="row align-items-center mb-5">
        <div class="col-md-12">
            <h2 class="fw-bold text-white mb-1">Dashboard Overview</h2>
            <p class="text-secondary small mb-0">Hello, <?php echo htmlspecialchars($username); ?>. Here's what's happening today.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-lg-<?php echo $role != 'student' ? '3' : '4'; ?>">
            <div class="card stat-card mb-0 h-100">
                <div class="stat-info">
                    <p class="stat-label"><?php echo $role == 'student' ? 'MY TOTAL COMPLAINTS' : 'TOTAL SYSTEM COMPLAINTS'; ?></p>
                    <h2 class="stat-value"><?php echo $total_complaints; ?></h2>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-<?php echo $role != 'student' ? '3' : '4'; ?>">
            <div class="card stat-card mb-0 h-100">
                <div class="stat-info">
                    <p class="stat-label"><?php echo $role == 'student' ? 'MY PENDING COMPLAINTS' : 'PENDING ACTION'; ?></p>
                    <h2 class="stat-value"><?php echo $pending_complaints; ?></h2>
                </div>
                <div class="stat-icon grey">
                    <i class="far fa-clock"></i>
                </div>
            </div>
        </div>
        <?php if ($role != 'student'): ?>
        <div class="col-lg-3">
            <div class="card stat-card mb-0 h-100 border-start border-4 border-warning">
                <div class="stat-info">
                    <p class="stat-label">STUDENT VERIFICATIONS</p>
                    <h2 class="stat-value text-warning"><?php echo $pending_verifications; ?></h2>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-lg-<?php echo $role != 'student' ? '3' : '4'; ?>">
            <div class="card stat-card mb-0 h-100">
                <div class="stat-info">
                    <p class="stat-label">RESOLVED CASES</p>
                    <h2 class="stat-value text-success"><?php echo $resolved_complaints; ?></h2>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Complaints Table -->
        <div class="col-xl-8">
            <div class="card h-100" id="complaints-table">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-white fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i> Recent Complaints</h5>
                    <?php if ($role == 'student'): ?>
                        <a href="submit_complaint.php" class="btn btn-primary btn-sm px-4">Submit New</a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Title & Description</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date Submitted</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($complaints) > 0): ?>
                                    <?php foreach ($complaints as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-white mb-1"><?php echo htmlspecialchars($c['title']); ?></div>
                                                <?php if (isset($c['student_username'])): ?>
                                                    <div class="text-secondary small">Submitted by: <span class="text-info"><?php echo htmlspecialchars($c['student_username']); ?></span></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="text-secondary small"><?php echo htmlspecialchars($c['category_name']); ?></span></td>
                                            <td>
                                                <span class="badge rounded-pill badge-status-<?php echo $c['status']; ?> px-3 py-2 small">
                                                    <?php echo ucfirst($c['status']); ?>
                                                </span>
                                            </td>
                                            <td><span class="text-secondary small"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></span></td>
                                            <td class="text-end">
                                                <a href="view_complaint.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-info btn-sm px-3">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-secondary">
                                            <i class="fas fa-folder-open fs-2 mb-3"></i><br>
                                            No complaints found in the database.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Components -->
        <div class="col-xl-4">
            <!-- System Status Card -->
            <div class="card mb-4 border-0" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%);">
                <div class="card-header border-0 pb-0">
                    <h6 class="mb-0 text-white fw-bold">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small">Database Connection</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Connected</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small">Session Status</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Last Action</span>
                        <span class="text-white small fw-semibold">Just now</span>
                    </div>
                </div>
            </div>

            <!-- Announcements/Quick Tips -->
            <div class="card mb-0">
                <div class="card-header">
                    <h6 class="mb-0 text-white fw-bold">Recent Announcements</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item bg-transparent border-secondary py-3 px-4">
                            <h6 class="text-info small fw-bold mb-1">System Version 2.0 Launched</h6>
                            <p class="text-secondary small mb-0 opacity-75">The complaint management system has been updated with a high-fidelity dark theme for better visibility.</p>
                        </li>
                        <li class="list-group-item bg-transparent border-secondary py-3 px-4">
                            <h6 class="text-warning small fw-bold mb-1">Upcoming Maintenance</h6>
                            <p class="text-secondary small mb-0 opacity-75">Scheduled database maintenance this Sunday between 02:00 AM and 04:00 AM. System may be intermittent.</p>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-secondary text-center">
                    <a href="#" class="text-primary small text-decoration-none fw-bold">View All Announcements <i class="fas fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
