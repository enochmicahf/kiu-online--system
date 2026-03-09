<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $reg_number = trim($_POST['reg_number']);
    $year = $_POST['year'];
    $semester = $_POST['semester'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR (reg_number = ? AND reg_number != '')");
        $stmt->execute([$username, $email, $reg_number]);
        if ($stmt->fetch()) {
            $error = "Username, Email, or Registration Number already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, reg_number, year, semester) VALUES (?, ?, ?, 'student', ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $reg_number, $year, $semester])) {
                $success = "Registration successful. <a href='login.php' class='text-primary'>Login here</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KIU Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark">
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 100vh;">
    <div class="card bg-dark border-secondary p-4" style="width: 100%; max-width: 500px; border-radius: 12px;">
        <div class="text-center mb-4">
            <h1 class="text-primary fw-bold mb-1" style="letter-spacing: -1px; font-size: 2.5rem;">KIU</h1>
            <h5 class="text-white opacity-75 fw-semibold mb-4">Online Complaint System</h5>
            <h4 class="text-white fw-bold">Student Registration</h4>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success py-2 small"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Username</label>
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Reg Number</label>
                    <input type="text" name="reg_number" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. 2024/BIT/001">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small text-secondary">Email</label>
                <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Academic Year</label>
                    <select name="year" class="form-select bg-dark text-white border-secondary small" required>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                        <option value="5">Year 5</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Semester</label>
                    <select name="semester" class="form-select bg-dark text-white border-secondary small" required>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-secondary">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark text-white border-secondary" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2" style="background-color: #3b82f6; border: none;">Register</button>
        </form>

        <div class="mt-4 text-center">
            <span class="text-secondary small">Already have an account?</span>
            <a href="login.php" class="text-decoration-none small fw-bold" style="color: #3b82f6;">Login</a>
        </div>
    </div>
</div>
</body>
</html>
