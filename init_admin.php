<?php
require_once 'includes/db.php';
require_once 'includes/security.php';

if (!current_user_is_admin()) {
    redirect_to('dashboard.php');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $allowedRoles = ['staff', 'admin'];

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Username, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, $allowedRoles, true)) {
        $error = 'Select a valid privileged role.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = 'A user with that username or email already exists.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$username, $email, $hashedPassword, $role]);
                $message = ucfirst($role) . ' account created successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to create the privileged account right now.';
        }
    }
}

$page_title = "Create Staff/Admin Account - KIU Support";
$current_page = 'users';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-secondary p-3">
                <h5 class="mb-0 text-white fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i> Create Privileged Account</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo csrf_input(); ?>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-secondary">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-secondary">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-secondary">Role</label>
                        <select name="role" class="form-select">
                            <option value="staff" <?php echo (($_POST['role'] ?? 'staff') === 'staff') ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="admin_users.php" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
