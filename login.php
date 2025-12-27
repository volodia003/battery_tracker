<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        if (login($username, $password)) {
            redirect('index.php');
        } else {
            $error = 'Неверное имя пользователя или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?= getTheme() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Battery Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: background 0.3s ease;
        }
        
        .auth-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-header i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .theme-toggle-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            z-index: 10;
        }
        
        .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(180deg);
        }
        
        /* Dark theme styles for auth pages */
        [data-theme="dark"] .auth-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
        
        [data-theme="dark"] .auth-card {
            background: #2d2d2d;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
        }
        
        [data-theme="dark"] .auth-body {
            background: #2d2d2d;
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .form-label {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .form-control {
            background-color: #383838;
            border-color: #505050;
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .form-control:focus {
            background-color: #404040;
            border-color: #667eea;
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .form-control::placeholder {
            color: #888;
        }
        
        [data-theme="dark"] .input-group-text {
            background-color: #383838;
            border-color: #505050;
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .text-muted {
            color: #888 !important;
        }
        
        [data-theme="dark"] .alert-danger {
            background-color: #3d2020;
            border-color: #5d3030;
            color: #ff5252;
        }
        
        [data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <button class="theme-toggle-btn" onclick="toggleTheme()" title="Сменить тему">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
        
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-battery-charging"></i>
                <h2 class="mb-0">Battery Tracker</h2>
                <p class="mb-0 mt-2">Вход в систему</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Введите имя пользователя" required autofocus
                                   value="<?= sanitize($_POST['username'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Введите пароль" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Войти
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Нет аккаунта? 
                        <a href="register.php" class="text-decoration-none">Зарегистрироваться</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            fetch('toggle_theme.php')
                .then(response => response.json())
                .then(data => {
                    document.documentElement.setAttribute('data-theme', data.theme);
                    updateThemeIcon(data.theme);
                    applyTheme(data.theme);
                });
        }
        
        function updateThemeIcon(theme) {
            const icon = document.querySelector('.theme-toggle-btn i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'bi bi-sun-fill';
                } else {
                    icon.className = 'bi bi-moon-stars-fill';
                }
            }
        }
        
        function applyTheme(theme) {
            // Force reflow to ensure styles are applied
            document.body.style.display = 'none';
            document.body.offsetHeight; // Trigger reflow
            document.body.style.display = '';
        }
        
        // Set initial theme
        document.addEventListener('DOMContentLoaded', function() {
            const theme = document.documentElement.getAttribute('data-theme');
            updateThemeIcon(theme);
            applyTheme(theme);
        });
    </script>
</body>
</html>
