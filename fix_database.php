<?php
require_once 'includes/db.php';

echo "Checking and fixing database schema...\n";

try {
    // Check if reg_number exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'reg_number'");
    if (!$stmt->fetch()) {
        echo "Adding 'reg_number' to 'users' table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN reg_number VARCHAR(50) AFTER role");
    }

    // Check if year exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'year'");
    if (!$stmt->fetch()) {
        echo "Adding 'year' to 'users' table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN year INT AFTER reg_number");
    }

    // Check if semester exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'semester'");
    if (!$stmt->fetch()) {
        echo "Adding 'semester' to 'users' table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN semester INT AFTER year");
    }

    // Check if is_verified exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if (!$stmt->fetch()) {
        echo "Adding 'is_verified' to 'users' table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER semester");
    }

    echo "Database schema is up to date.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
