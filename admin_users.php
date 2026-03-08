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
    <div class="card-header bg-white p-3">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> All Registered Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
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
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="text-danger" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
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
