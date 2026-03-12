<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - KIU Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            height: 100vh;
            background: radial-gradient(circle at top right, #111827, #05080f);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 60px;
            max-width: 800px;
            width: 90%;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="hero-section">
        <div class="hero-card">
            <h1 class="text-primary fw-bold mb-1" style="font-size: 4rem; letter-spacing: -2px;">KIU</h1>
            <h4 class="text-white fw-semibold mb-4 opacity-75">Online Student Complaint Registration and Management System</h4>
            <p class="text-secondary mb-5 fs-5">Kampala International University's official platform for student support and administrative resolution. Submit, track, and resolve grievances with ease.</p>

            <div class="d-flex flex-wrap justify-content-center gap-4">
                <a href="login.php" class="btn btn-primary px-5 py-3 fs-5 fw-bold">Login to Account</a>
                <a href="register.php" class="btn btn-outline-light px-5 py-3 fs-5 fw-bold" style="border-radius: 12px; border-color: rgba(255,255,255,0.1);">Create Student ID</a>
            </div>

            <div class="mt-5 pt-4 border-top border-secondary border-opacity-10">
                <p class="text-secondary small mb-0">System Controller? <a href="login.php" class="text-primary text-decoration-none fw-bold">Login as Admin</a></p>
            </div>
        </div>
    </div>
</body>
</html>
