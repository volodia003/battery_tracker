<?php
/**
 * Setup script to create the default user
 * Run this once to add the default user123 with password pas123
 */

require_once 'config/database.php';

// Generate password hash
$username = 'user123';
$password = 'pas123';
$email = 'user123@example.com';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $existing = db()->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    
    if ($existing) {
        echo "✓ User '$username' already exists!\n";
    } else {
        // Insert the default user
        db()->insert(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)",
            [$username, $hashedPassword, $email]
        );
        
        echo "✓ Successfully created user '$username' with password '$password'\n";
        echo "You can now login with these credentials.\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Please make sure the database is set up correctly.\n";
}
