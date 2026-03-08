<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch complaint details
$stmt = $pdo->prepare("SELECT c.*, u.username as student_name, u.reg_number, u.year, u.semester, cat.name as category_name
                       FROM complaints c
                       JOIN users u ON c.student_id = u.id
                       LEFT JOIN categories cat ON c.category_id = cat.id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$complaint = $stmt->fetch();

if (!$complaint || ($role == 'student' && $complaint['student_id'] != $user_id)) {
    die("Unauthorized or not found.");
}

// Handle Status Update and Admin Remarks (Staff only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && $role != 'student') {
    $new_status = $_POST['status'];
    $admin_remarks = trim($_POST['admin_remarks']);

    $stmt = $pdo->prepare("UPDATE complaints SET status = ?, admin_remarks = ? WHERE id = ?");
    $stmt->execute([$new_status, $admin_remarks, $id]);

    notify($complaint['student_id'], "Your complaint #$id has been reviewed. Status: " . ucfirst(str_replace('_', ' ', $new_status)));
    header("Location: view_complaint.php?id=$id&msg=updated");
    exit();
}

// Handle Feedback (Student only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback']) && $role == 'student' && $complaint['status'] == 'resolved') {
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    $stmt = $pdo->prepare("INSERT INTO feedback (complaint_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->execute([$id, $rating, $comment]);
    header("Location: view_complaint.php?id=$id&msg=feedback_sent");
    exit();
}

// Fetch feedback if any
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE complaint_id = ?");
$stmt->execute([$id]);
$feedback = $stmt->fetch();

$page_title = "Complaint #$id - KIU Complaints";
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                    <span class="badge <?php echo $complaint['status'] == 'resolved' ? 'bg-success' : 'bg-warning'; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                    </span>
                </div>
                <hr>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($complaint['category_name']); ?></p>
                <p>
                    <strong>Submitted by:</strong> <?php echo htmlspecialchars($complaint['student_name']); ?>
                    <?php if ($complaint['reg_number']): ?>
                        (<?php echo htmlspecialchars($complaint['reg_number']); ?>, Year <?php echo $complaint['year']; ?>, Sem <?php echo $complaint['semester']; ?>)
                    <?php endif; ?>
                    on <?php echo $complaint['created_at']; ?>
                </p>
                <div class="p-3 bg-light rounded mb-3">
                    <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
                </div>
                <?php if ($complaint['attachment']): ?>
                    <p><strong>Attachment:</strong> <a href="<?php echo $complaint['attachment']; ?>" target="_blank" class="btn btn-outline-dark btn-sm">View File</a></p>
                <?php endif; ?>

                <?php if ($complaint['admin_remarks']): ?>
                    <div class="mt-4 p-3 bg-gold-light border-start border-4 border-warning rounded">
                        <h5><i class="fas fa-comment-dots me-2"></i> Staff Feedback</h5>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($role == 'student' && $complaint['status'] == 'resolved' && !$feedback): ?>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5>Submit Feedback</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Rating (1-5)</label>
                            <select name="rating" class="form-select" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Very Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comment" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        <?php elseif ($feedback): ?>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5>Student Feedback</h5>
                    <p><strong>Rating:</strong> <?php echo $feedback['rating']; ?>/5</p>
                    <p><strong>Comment:</strong> <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <?php if ($role != 'student'): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">Review Complaint</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Update Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $complaint['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $complaint['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Staff Feedback/Remarks</label>
                            <textarea name="admin_remarks" class="form-control" rows="4"><?php echo htmlspecialchars($complaint['admin_remarks'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-success w-100">Save Review</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <a href="dashboard.php" class="btn btn-outline-secondary w-100">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
