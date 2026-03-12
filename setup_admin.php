<?php
require_once 'includes/db.php';

echo "Setting up default administrator...\n";

try {
    $username = 'admin';
    $password = 'admin';
    $email = 'admin@kiu.ac.ug';

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if (!$stmt->fetch()) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute([$username, $email, $hashed]);
        echo "Default admin account created: admin / admin\n";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', is_verified = 1 WHERE username = ?");
        $stmt->execute([$hashed, $username]);
        echo "Admin account already exists. Password has been reset to 'admin'.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
