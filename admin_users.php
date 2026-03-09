<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle User Role Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $target_user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Prevent admin from changing their own role to something else
    if ($target_user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $target_user_id]);
        header("Location: admin_users.php?msg=updated");
        exit();
    }
}

// Handle Student Verification
if (isset($_GET['verify']) && is_numeric($_GET['verify'])) {
    $target_user_id = $_GET['verify'];
    $status = $_GET['status'] ?? 1;
    $stmt = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ? AND role = 'student'");
    $stmt->execute([$status, $target_user_id]);
    header("Location: admin_users.php?msg=verification_updated");
    exit();
}

// Handle User Deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $target_user_id = $_GET['delete'];
    if ($target_user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$target_user_id]);
        header("Location: admin_users.php?msg=deleted");
        exit();
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$page_title = "User Management - KIU Support";
$current_page = 'users';
include 'includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent border-secondary p-3">
        <h5 class="mb-0 text-white fw-bold"><i class="fas fa-users me-2 text-primary"></i> All Registered Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>User & Academic Info</th>
                        <th>Email</th>
                        <th>Verification</th>
                        <th style="width: 150px;">Role</th>
                        <th>Joined</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($u['username']); ?></div>
                            <?php if($u['role'] == 'student'): ?>
                                <small class="text-info"><?php echo htmlspecialchars($u['reg_number'] ?? 'N/A'); ?> | Yr <?php echo $u['year'] ?? '?'; ?> Sem <?php echo $u['semester'] ?? '?'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-secondary"><?php echo htmlspecialchars($u['email']); ?></small></td>
                        <td>
                            <?php if($u['role'] == 'student'): ?>
                                <?php if($u['is_verified']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                        <i class="fas fa-check-circle me-1"></i> Verified
                                    </span>
                                    <a href="admin_users.php?verify=<?php echo $u['id']; ?>&status=0" class="ms-2 text-warning small text-decoration-none">Revoke</a>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                        <i class="fas fa-times-circle me-1"></i> Pending
                                    </span>
                                    <a href="admin_users.php?verify=<?php echo $u['id']; ?>&status=1" class="btn btn-primary btn-sm py-0 px-2 ms-2" style="font-size: 0.7rem;">Verify Student</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-secondary small">--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="student" <?php if($u['role'] == 'student') echo 'selected'; ?>>Student</option>
                                    <option value="staff" <?php if($u['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                                    <option value="admin" <?php if($u['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_role" value="1">
                            </form>
                        </td>
                        <td><small class="text-secondary"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></small></td>
                        <td class="text-end">
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="text-danger opacity-50 hover-opacity-100" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
