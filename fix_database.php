<?php
require_once 'includes/db.php';
require_once 'includes/security.php';

$setupToken = ensure_admin_or_setup_token(
    'Database Maintenance',
    'Unlock this maintenance tool with the configured setup token or an active administrator session.'
);

$output = [];
$output[] = "Checking and fixing database schema...";

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'reg_number'");
    if (!$stmt->fetch()) {
        $output[] = "Adding 'reg_number' to 'users' table...";
        $pdo->exec("ALTER TABLE users ADD COLUMN reg_number VARCHAR(50) AFTER role");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'year'");
    if (!$stmt->fetch()) {
        $output[] = "Adding 'year' to 'users' table...";
        $pdo->exec("ALTER TABLE users ADD COLUMN year INT AFTER reg_number");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'semester'");
    if (!$stmt->fetch()) {
        $output[] = "Adding 'semester' to 'users' table...";
        $pdo->exec("ALTER TABLE users ADD COLUMN semester INT AFTER year");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if (!$stmt->fetch()) {
        $output[] = "Adding 'is_verified' to 'users' table...";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER semester");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        priority ENUM('info', 'warning', 'danger') DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $output[] = "Database schema is up to date.";
} catch (Exception $e) {
    $output[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Maintenance</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 40px 20px;">
    <div style="max-width: 720px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);">
        <h1 style="margin-top:0;">Database Maintenance</h1>
        <pre style="white-space: pre-wrap; margin:0; color:#0f172a;"><?php echo htmlspecialchars(implode("\n", $output)); ?></pre>
        <?php if ($setupToken !== ''): ?>
            <p style="margin-top:18px;color:#475569;">Remember to clear <code>ADMIN_SETUP_TOKEN</code> in <code>includes/config.php</code> after setup is complete.</p>
        <?php endif; ?>
    </div>
</body>
</html>
