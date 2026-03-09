<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $student_id = $_SESSION['user_id'];

    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $file_ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_extensions)) {
            if (!is_dir(UPLOADS_DIR)) {
                mkdir(UPLOADS_DIR, 0777, true);
            }
            $file_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            $attachment_path = UPLOADS_DIR . $file_name;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path);
        } else {
            $error = "Invalid file type. Only JPG, PNG, PDF, and DOC are allowed.";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO complaints (student_id, category_id, title, description, attachment) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $category_id, $title, $description, $attachment_path])) {
            // Simple notification for staff
            if (function_exists('notify')) {
                $stmt_staff = $pdo->query("SELECT id FROM users WHERE role = 'staff'");
                while ($staff = $stmt_staff->fetch()) {
                    notify($staff['id'], "New complaint submitted by " . $_SESSION['username'] . ": " . $title);
                }
            }
            $success = "Complaint submitted successfully! <a href='dashboard.php' class='text-primary fw-bold text-decoration-none'>Go to Dashboard</a>";
        } else {
            $error = "Failed to submit complaint. Please try again.";
        }
    }
}

$page_title = "Submit Complaint - KIU Support";
$current_page = 'complaints';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-transparent border-secondary">
                <h4 class="mb-0 text-white fw-bold"><i class="fas fa-edit me-2 text-primary"></i> Submit a Complaint</h4>
                <p class="text-secondary small mb-0 mt-1">Provide as much detail as possible to help us resolve your issue.</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 py-3 mb-4 rounded-3 d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 py-3 mb-4 rounded-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
                        <div><?php echo $success; ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Complaint Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="A short, descriptive summary of the issue">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM categories");
                            while ($cat = $stmt->fetch()) {
                                echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="8" required placeholder="Describe the problem, when it occurred, and any steps you've already taken..."></textarea>
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Supporting Documents (Optional)</label>
                        <div class="input-group">
                            <input type="file" name="attachment" class="form-control">
                            <span class="input-group-text bg-dark border-secondary text-secondary small">JPG, PNG, PDF, DOC</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-3">
                        <a href="dashboard.php" class="btn btn-outline-secondary px-4 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2">Submit Complaint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
