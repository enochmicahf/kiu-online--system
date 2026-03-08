<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && is_array($user) && isset($user['password']) && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KIU Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark">
<div class="auth-wrapper d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card bg-dark border-secondary p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
        <div class="text-center mb-4">
            <div class="logo-box mb-3" style="background: #fff; padding: 10px; border-radius: 6px; display: inline-block;">
                <div class="logo-text" style="color: #000; font-weight: 800; font-size: 1.4rem; font-style: italic;">VJ<span style="color: #e11d48;">Pr</span>one</div>
            </div>
            <h4 class="text-white fw-bold">Login</h4>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-secondary">Username</label>
                <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small text-secondary">Password</label>
                <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #3b82f6; border: none;">Login</button>
        </form>

        <div class="mt-4 text-center">
            <span class="text-secondary small">Don't have an account?</span>
            <a href="register.php" class="text-decoration-none small fw-bold" style="color: #3b82f6;">Register</a>
        </div>
    </div>
</div>
</body>
</html>
