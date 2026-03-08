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
        // Simple notification for staff (this could be improved to notify specific staff)
        $stmt_staff = $pdo->query("SELECT id FROM users WHERE role = 'staff'");
        while ($staff = $stmt_staff->fetch()) {
            notify($staff['id'], "New complaint submitted by " . $_SESSION['username'] . ": " . $title);
        }
            $success = "Complaint submitted successfully. <a href='dashboard.php'>Go to Dashboard</a>";
        } else {
            $error = "Failed to submit complaint.";
        }
    }
}

$page_title = "Submit Complaint - KIU Complaints";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <h3>Submit a Complaint</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Short summary of your issue">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM categories");
                            while ($cat = $stmt->fetch()) {
                                echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <div class="d-grid gap-2 d-md-block">
                        <button type="submit" class="btn btn-primary">Submit Complaint</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
