<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = 'Пожалуйста, заполните все обязательные поля';
    } elseif (strlen($username) < 3) {
        $error = 'Имя пользователя должно быть не менее 3 символов';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } elseif ($password !== $confirmPassword) {
        $error = 'Пароли не совпадают';
    } else {
        $result = register($username, $password, $email);
        
        if ($result['success']) {
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?= getTheme() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Battery Tracker</title>
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
            padding: 2rem 0;
        }
        
        .auth-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            margin: 1rem;
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
        }
        
        .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(180deg);
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
                <p class="mb-0 mt-2">Регистрация</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Минимум 3 символа" required autofocus
                                   value="<?= sanitize($_POST['username'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (необязательно)</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="example@mail.com"
                                   value="<?= sanitize($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Минимум 6 символов" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Подтвердите пароль <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Повторите пароль" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-person-plus me-2"></i>
                        Зарегистрироваться
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Уже есть аккаунт? 
                        <a href="login.php" class="text-decoration-none">Войти</a>
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
                });
        }
        
        function updateThemeIcon(theme) {
            const icon = document.querySelector('.theme-toggle-btn i');
            if (theme === 'dark') {
                icon.className = 'bi bi-sun-fill';
            } else {
                icon.className = 'bi bi-moon-stars-fill';
            }
        }
        
        // Set initial icon
        updateThemeIcon(document.documentElement.getAttribute('data-theme'));
    </script>
</body>
</html>
