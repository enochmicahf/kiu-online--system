<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$page_title = "Dashboard - KIU Complaints";
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo ucfirst($role); ?>)</h2>
        <hr>

        <?php if ($role == 'student'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>My Complaints</h3>
                <a href="submit_complaint.php" class="btn btn-success">Submit New Complaint</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT c.*, cat.name as category_name FROM complaints c LEFT JOIN categories cat ON c.category_id = cat.id WHERE c.student_id = ? ORDER BY c.created_at DESC");
                            $stmt->execute([$user_id]);
                            $complaints = $stmt->fetchAll();

                            foreach ($complaints as $complaint):
                                $badge_class = 'bg-warning';
                                if ($complaint['status'] == 'resolved') $badge_class = 'bg-success';
                                if ($complaint['status'] == 'in_progress') $badge_class = 'bg-info';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['category_name']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                <td><a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                            <?php endforeach; if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center">No complaints found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: // Staff or Admin ?>
            <h3>Management Dashboard</h3>
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT c.*, u.username as student_name, cat.name as category_name FROM complaints c JOIN users u ON c.student_id = u.id LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY c.created_at DESC");
                            $complaints = $stmt->fetchAll();

                            foreach ($complaints as $complaint):
                                $badge_class = 'bg-warning';
                                if ($complaint['status'] == 'resolved') $badge_class = 'bg-success';
                                if ($complaint['status'] == 'in_progress') $badge_class = 'bg-info';
                            ?>
                            <tr>
                                <td><?php echo $complaint['id']; ?></td>
                                <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['category_name']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                <td><a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary">Manage</a></td>
                            </tr>
                            <?php endforeach; if (empty($complaints)): ?>
                            <tr><td colspan="7" class="text-center">No complaints found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
