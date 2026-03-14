<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/security.php';

if (!isset($_SESSION['user_id'])) {
    redirect_to('login.php');
}

$id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch complaint details
$stmt = $pdo->prepare("SELECT c.*, u.username as student_name, u.reg_number, u.year, u.semester, cat.name as category_name
                       FROM complaints c
                       LEFT JOIN users u ON c.student_id = u.id
                       LEFT JOIN categories cat ON c.category_id = cat.id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$complaint = $stmt->fetch();

if (!$complaint || ($role == 'student' && $complaint['student_id'] != $user_id)) {
    die("Unauthorized or not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();
}

// Handle Status Update and Admin Remarks (Staff/Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && $role != 'student') {
    $new_status = $_POST['status'] ?? '';
    $admin_remarks = trim($_POST['admin_remarks']);
    $allowed_statuses = ['pending', 'approved', 'in_progress', 'resolved', 'closed'];

    if (in_array($new_status, $allowed_statuses, true)) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = ?, admin_remarks = ? WHERE id = ?");
        $stmt->execute([$new_status, $admin_remarks, $id]);

        if (function_exists('notify')) {
            notify($complaint['student_id'], "Your complaint #$id has been reviewed. Status: " . strtoupper(str_replace('_', ' ', $new_status)));
        }
    }
    redirect_to("view_complaint.php?id=$id&msg=updated");
}

// Handle Feedback (Student only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback']) && $role == 'student' && $complaint['status'] == 'resolved') {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO feedback (complaint_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->execute([$id, $rating, $comment]);
        redirect_to("view_complaint.php?id=$id&msg=feedback_sent");
    }
}

// Fetch feedback if any
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE complaint_id = ?");
$stmt->execute([$id]);
$feedback = $stmt->fetch();

$page_title = "Complaint Details";
$current_page = 'complaints';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header border-bottom border-secondary bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($complaint['title']); ?></h5>
                <?php
                    $status_class = match($complaint['status']) {
                        'pending' => 'bg-warning text-dark',
                        'approved' => 'bg-primary',
                        'in_progress' => 'bg-info text-dark',
                        'resolved' => 'bg-success',
                        'closed' => 'bg-secondary',
                        default => 'bg-dark'
                    };
                ?>
                <span class="badge badge-custom <?php echo $status_class; ?>">
                    <?php echo strtoupper(str_replace('_', ' ', $complaint['status'])); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-secondary d-block">Category</small>
                        <span><?php echo htmlspecialchars($complaint['category_name']); ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-secondary d-block">Submitted On</small>
                        <span><?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></span>
                    </div>
                </div>
                <div class="mb-4">
                    <small class="text-secondary d-block">Student Information</small>
                    <span class="fw-bold"><?php echo htmlspecialchars($complaint['student_name']); ?></span>
                    <?php if ($complaint['reg_number']): ?>
                        <span class="text-secondary">(<?php echo htmlspecialchars($complaint['reg_number']); ?>, Year <?php echo $complaint['year']; ?>, Sem <?php echo $complaint['semester']; ?>)</span>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <small class="text-secondary d-block mb-1">Description</small>
                    <div class="p-3 rounded" style="background: #1e293b;">
                        <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
                    </div>
                </div>

                <?php if ($complaint['attachment']): ?>
                    <div class="mb-4">
                        <small class="text-secondary d-block mb-2">Attachment</small>
                        <a href="<?php echo $complaint['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-paperclip me-2"></i>View Attachment</a>
                    </div>
                <?php endif; ?>

                <?php if ($complaint['admin_remarks']): ?>
                    <div class="mt-4 p-3 rounded border-start border-4 border-info" style="background: rgba(59, 130, 246, 0.1);">
                        <h6 class="text-info"><i class="fas fa-comment-dots me-2"></i> Staff Feedback</h6>
                        <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($role == 'student' && $complaint['status'] == 'resolved' && !$feedback): ?>
            <div class="card mb-4">
                <div class="card-header border-bottom border-secondary bg-transparent py-3">
                    <h5 class="mb-0">Rate Service</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Rating (1-5)</label>
                            <select name="rating" class="form-select bg-dark text-white border-secondary">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Very Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Comments</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Share your thoughts..."></textarea>
                        </div>
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        <?php elseif ($feedback): ?>
            <div class="card mb-4">
                <div class="card-header border-bottom border-secondary bg-transparent py-3">
                    <h5 class="mb-0">Student Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-warning">
                            <?php for($i=0; $i<$feedback['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                        </span>
                        <span class="text-secondary ms-2">(<?php echo $feedback['rating']; ?>/5)</span>
                    </div>
                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <?php if ($role != 'student'): ?>
            <div class="card mb-4">
                <div class="card-header border-bottom border-secondary bg-transparent py-3">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Current Status</label>
                            <select name="status" class="form-select bg-dark text-white border-secondary">
                                <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $complaint['status'] == 'approved' ? 'selected' : ''; ?> style="background: #3b82f6; color: white; font-weight: bold;">Approved (Finalize Details)</option>
                                <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $complaint['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Staff Remarks</label>
                            <textarea name="admin_remarks" class="form-control" rows="4"><?php echo htmlspecialchars($complaint['admin_remarks'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-info w-100 fw-bold">Update Record</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <a href="dashboard.php" class="btn btn-outline-secondary w-100">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
