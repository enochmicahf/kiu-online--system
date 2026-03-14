<?php
require_once 'includes/db.php';

echo "<body style='font-family: Arial; background: #f4f4f4; padding: 50px; text-align: center;'>";
echo "<div style='background: white; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
echo "<h2>KIU Admin Setup</h2>";

try {
    $username = 'admin';
    $password = 'admin';
    $email = 'admin@kiu.ac.ug';

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute([$username, $email, $hashed]);
        echo "<h3 style='color: green;'>✅ Success!</h3>";
        echo "<p>Admin account created successfully.</p>";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', is_verified = 1 WHERE username = ?");
        $stmt->execute([$hashed, $username]);
        echo "<h3 style='color: green;'>✅ Password Reset!</h3>";
        echo "<p>Admin account updated. Password is now <b>admin</b>.</p>";
    }

    echo "<p>Username: <b>admin</b><br>Password: <b>admin</b></p>";
    echo "<a href='login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;'>Go to Login Page</a>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Ensure you have created the database and imported <b>database.sql</b>.</p>";
}

echo "</div></body>";
?>
