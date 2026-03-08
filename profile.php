<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $reg_number = trim($_POST['reg_number'] ?? '');
    $year = $_POST['year'] ?? null;
    $semester = $_POST['semester'] ?? null;

    $stmt = $pdo->prepare("UPDATE users SET email = ?, reg_number = ?, year = ?, semester = ? WHERE id = ?");
    if ($stmt->execute([$new_email, $reg_number, $year, $semester, $user_id])) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$page_title = "My Profile - KIU Support";
$current_page = 'profile';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white p-3">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> My Profile</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Username cannot be changed.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <?php if ($user['role'] == 'student'): ?>
                    <div class="mb-3">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="reg_number" class="form-control" value="<?php echo htmlspecialchars($user['reg_number'] ?? ''); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($user['year'] == $i) ? 'selected' : ''; ?>>Year <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <?php for($i=1; $i<=3; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($user['semester'] == $i) ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Joined On</label>
                        <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
