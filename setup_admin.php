<?php
require_once 'includes/db.php';
require_once 'includes/security.php';

$setupToken = ensure_admin_or_setup_token(
    'Admin Setup',
    'Use the setup token to unlock the administrator setup screen.'
);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    require_valid_csrf_token();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Username, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $existingUserId = $stmt->fetchColumn();

            if ($existingUserId) {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ?, role = 'admin', is_verified = 1 WHERE id = ?");
                $stmt->execute([$email, $hashedPassword, $existingUserId]);
                $message = 'Administrator account updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
                $stmt->execute([$username, $email, $hashedPassword]);
                $message = 'Administrator account created successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to save the administrator account. Check that the username and email are unique.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - KIU Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-2">Administrator Setup</h1>
                    <p class="text-secondary mb-4">Create the initial administrator account or reset an existing one after unlocking this page with the configured setup token.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($message); ?><br>
                            <span class="small">Log in with the credentials you just set, then clear <code>ADMIN_SETUP_TOKEN</code> in <code>includes/config.php</code>.</span>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php if ($setupToken !== ''): ?>
                            <input type="hidden" name="setup_token" value="<?php echo htmlspecialchars($setupToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="create_admin" value="1">

                        <div class="mb-3">
                            <label class="form-label">Admin Username</label>
                            <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@kiu.ac.ug'); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Administrator Account</button>
                    </form>

                    <div class="mt-4 small text-secondary">
                        <strong>Recommended:</strong> clear or rotate <code>ADMIN_SETUP_TOKEN</code> after setup is complete so this page is locked again.
                    </div>

                    <?php if ($message): ?>
                        <div class="mt-3 text-center">
                            <a href="login.php" class="btn btn-outline-secondary">Go to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
