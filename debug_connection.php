<?php
require_once 'includes/db.php';
require_once 'includes/security.php';

$setupToken = ensure_admin_or_setup_token(
    'System Debugger',
    'Unlock this diagnostic page with the configured setup token or an active administrator session.'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Debugger</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 40px 20px;">
    <div style="max-width: 720px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);">
        <h1 style="margin-top:0;">System Debugger</h1>

        <?php
        try {
            $pdo->query("SELECT 1");
            echo "<p style='color: green;'>Database connection successful.</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Please check <b>includes/config.php</b> and ensure your database exists.</p>";
            echo "</div></body></html>";
            exit();
        }

        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $count = $stmt->fetchColumn();
            echo "<p>'users' table found. Total users: " . htmlspecialchars((string) $count) . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>'users' table not found.</p>";
            echo "<p>Please run <b>database.sql</b> in your MySQL tool and then open <a href='fix_database.php'>fix_database.php</a>.</p>";
            echo "</div></body></html>";
            exit();
        }

        $stmt = $pdo->query("SELECT username, role, is_verified FROM users WHERE role = 'admin' ORDER BY id ASC");
        $admins = $stmt->fetchAll();

        if ($admins) {
            echo "<p style='color: green;'>Administrator account(s) found.</p>";
            echo "<ul>";
            foreach ($admins as $admin) {
                echo "<li>";
                echo "Username: " . htmlspecialchars($admin['username']) . " | Role: " . htmlspecialchars($admin['role']) . " | Verified: " . ($admin['is_verified'] ? 'Yes' : 'No');
                echo "</li>";
            }
            echo "</ul>";
            echo "<p>Log in at <a href='login.php'>login.php</a> with the administrator credentials you configured during setup.</p>";
        } else {
            echo "<p style='color: orange;'>No administrator accounts were found.</p>";
            echo "<p>Please run <a href='setup_admin.php'>setup_admin.php</a> to create the first administrator account.</p>";
        }
        ?>

        <?php if ($setupToken !== ''): ?>
            <p style="margin-top:20px;color:#475569;">Clear <code>ADMIN_SETUP_TOKEN</code> in <code>includes/config.php</code> when you finish debugging.</p>
        <?php endif; ?>
    </div>
</body>
</html>
