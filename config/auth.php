<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return db()->fetchOne("SELECT id, username, email, created_at FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// Login user
function login($username, $password) {
    $user = db()->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    
    if (!$user) {
        return false;
    }
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

// Register new user
function register($username, $password, $email = null) {
    // Check if username already exists
    $existing = db()->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    
    if ($existing) {
        return ['success' => false, 'message' => 'Имя пользователя уже занято'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $userId = db()->insert(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)",
            [$username, $hashedPassword, $email]
        );
        
        // Auto login after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        
        return ['success' => true, 'message' => 'Регистрация успешна'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Ошибка при регистрации'];
    }
}

// Logout user
function logout() {
    session_unset();
    session_destroy();
}

// Require authentication (redirect to login if not logged in)
function requireAuth() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Get or set theme preference
function getTheme() {
    return $_SESSION['theme'] ?? 'light';
}

function setTheme($theme) {
    $_SESSION['theme'] = $theme;
}

function toggleTheme() {
    $currentTheme = getTheme();
    $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
    setTheme($newTheme);
    return $newTheme;
}
