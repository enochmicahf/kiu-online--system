<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Statistics for Dashboard
$total_complaints = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$pending_complaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
$resolved_complaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Specific queries based on role
if ($role == 'student') {
    $stmt = $pdo->prepare("SELECT c.*, cat.name as category_name FROM complaints c LEFT JOIN categories cat ON c.category_id = cat.id WHERE c.student_id = ? ORDER BY c.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_complaints = $stmt->fetchAll();

    $user_total = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ?");
    $user_total->execute([$user_id]);
    $user_total_count = $user_total->fetchColumn();

    $user_pending = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status = 'pending'");
    $user_pending->execute([$user_id]);
    $user_pending_count = $user_pending->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT c.*, u.username as student_name, cat.name as category_name FROM complaints c JOIN users u ON c.student_id = u.id LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY c.created_at DESC LIMIT 5");
    $recent_complaints = $stmt->fetchAll();
}

$page_title = "Dashboard - KIU Support";
$current_page = 'dashboard';
include 'includes/header.php';
?>

<div class="row g-4 mb-4">
    <?php if ($role == 'admin'): ?>
        <div class="col-md-3">
            <div class="card stat-card bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">Total Users</h6>
                        <h2 class="mb-0"><?php echo $total_users; ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gold">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">Total Complaints</h6>
                        <h2 class="mb-0"><?php echo $total_complaints; ?></h2>
                    </div>
                    <i class="fas fa-file-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">Pending</h6>
                        <h2 class="mb-0"><?php echo $pending_complaints; ?></h2>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">Resolved</h6>
                        <h2 class="mb-0"><?php echo $resolved_complaints; ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    <?php elseif ($role == 'student'): ?>
        <div class="col-md-6">
            <div class="card stat-card bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">My Total Complaints</h6>
                        <h2 class="mb-0"><?php echo $user_total_count; ?></h2>
                    </div>
                    <i class="fas fa-paper-plane fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card bg-gold">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small">My Pending Complaints</h6>
                        <h2 class="mb-0"><?php echo $user_pending_count; ?></h2>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Recent Complaints</h5>
                <?php if ($role == 'student'): ?>
                    <a href="submit_complaint.php" class="btn btn-primary btn-sm">Submit New</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php if ($role != 'student') echo "<th>Student</th>"; ?>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_complaints as $c):
                                $status_class = match($c['status']) {
                                    'pending' => 'bg-warning',
                                    'in_progress' => 'bg-info',
                                    'resolved' => 'bg-success',
                                    'closed' => 'bg-secondary',
                                    default => 'bg-dark'
                                };
                            ?>
                            <tr>
                                <?php if ($role != 'student') echo "<td>".htmlspecialchars($c['student_name'])."</td>"; ?>
                                <td><?php echo htmlspecialchars($c['title']); ?></td>
                                <td><?php echo htmlspecialchars($c['category_name']); ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                <td><a href="view_complaint.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <?php endforeach; if(empty($recent_complaints)): ?>
                                <tr><td colspan="6" class="text-center p-4">No recent complaints found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
