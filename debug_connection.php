<?php
require_once 'includes/db.php';

echo "<h1>System Debugger</h1>";

// 1. Check DB Connection
try {
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check <b>includes/config.php</b> and ensure your database 'kiu_complaints' exists.</p>";
    exit();
}

// 2. Check Users Table
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p>✅ 'users' table found. Total users: $count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 'users' table not found!</p>";
    echo "<p>Please run <b>database.sql</b> in your MySQL tool (like phpMyAdmin).</p>";
    exit();
}

// 3. Check Admin Account
$stmt = $pdo->prepare("SELECT username, role, is_verified FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<p style='color: green;'>✅ Administrator account 'admin' found!</p>";
    echo "<ul>";
    echo "<li>Role: " . $admin['role'] . "</li>";
    echo "<li>Verified: " . ($admin['is_verified'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    echo "<p>Try logging in at <a href='login.php'>login.php</a> with password: <b>admin</b></p>";
} else {
    echo "<p style='color: orange;'>⚠️ Administrator account 'admin' NOT found.</p>";
    echo "<p>Please run <a href='setup_admin.php'>setup_admin.php</a> to create the account.</p>";
}
?>
