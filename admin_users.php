<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/security.php';

if (!current_user_is_admin()) {
    redirect_to('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();
}

// Handle User Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    $new_role = $_POST['role'] ?? '';
    $allowed_roles = ['student', 'staff', 'admin'];

    // Prevent admin from changing their own role to something else
    if ($target_user_id > 0 && $target_user_id !== (int) $_SESSION['user_id'] && in_array($new_role, $allowed_roles, true)) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $target_user_id]);
        redirect_to('admin_users.php?msg=updated');
    }
}

// Handle Student Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_student'])) {
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    $status = (($_POST['status'] ?? '1') === '1') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ? AND role = 'student'");
    $stmt->execute([$status, $target_user_id]);
    redirect_to('admin_users.php?msg=verification_updated');
}

// Handle User Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    if ($target_user_id > 0 && $target_user_id !== (int) $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$target_user_id]);
        redirect_to('admin_users.php?msg=deleted');
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
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 rounded-0 mb-0">
                <?php
                $messages = [
                    'updated' => 'User role updated successfully.',
                    'verification_updated' => 'Student verification updated successfully.',
                    'deleted' => 'User deleted successfully.',
                ];
                echo htmlspecialchars($messages[$_GET['msg']] ?? 'Changes saved.');
                ?>
            </div>
        <?php endif; ?>
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
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                        <i class="fas fa-times-circle me-1"></i> Pending
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-secondary small">--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <?php echo csrf_input(); ?>
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" <?php echo ($u['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <option value="student" <?php if($u['role'] == 'student') echo 'selected'; ?>>Student</option>
                                    <option value="staff" <?php if($u['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                                    <option value="admin" <?php if($u['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_role" value="1">
                            </form>
                        </td>
                        <td><small class="text-secondary"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></small></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <?php if($u['role'] == 'student'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $u['is_verified'] ? '0' : '1'; ?>">
                                        <input type="hidden" name="verify_student" value="1">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none <?php echo $u['is_verified'] ? 'text-warning' : 'text-primary'; ?>">
                                            <?php echo $u['is_verified'] ? 'Revoke' : 'Verify'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="delete_user" value="1">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-link text-danger opacity-50 hover-opacity-100 p-0 border-0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
