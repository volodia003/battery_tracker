<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Tracker - Система учета аккумуляторов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .landing-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-section .lead {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .content-section {
            padding: 3rem 2rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 15px;
            background: #f8f9fa;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .feature-card h5 {
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        
        .btn-outline-primary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .benefits-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .benefits-section h4 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .benefit-item {
            display: flex;
            align-items: start;
            padding: 1rem 0;
        }
        
        .benefit-item i {
            color: #667eea;
            font-size: 1.5rem;
            margin-right: 1rem;
            margin-top: 0.2rem;
        }
        
        .benefit-item h6 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .benefit-item p {
            margin: 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="hero-section">
            <div class="hero-icon">
                <i class="bi bi-battery-charging"></i>
            </div>
            <h1>Battery Tracker</h1>
            <p class="lead">Система учета и мониторинга аккумуляторного оборудования</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="login.php" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Войти в систему
                </a>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-person-plus me-2"></i>Регистрация
                </a>
            </div>
        </div>
        
        <div class="content-section">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-phone"></i>
                        <h5>Учет устройств</h5>
                        <p class="text-muted mb-0">Отслеживайте состояние батарей телефонов, ноутбуков, наушников и другой техники</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-lightning-charge"></i>
                        <h5>Журнал зарядок</h5>
                        <p class="text-muted mb-0">Ведите историю зарядок с автоматическим расчетом деградации батареи</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-graph-up"></i>
                        <h5>Аналитика</h5>
                        <p class="text-muted mb-0">Просматривайте графики здоровья батарей и прогнозы срока службы</p>
                    </div>
                </div>
            </div>
            
            <div class="benefits-section">
                <h4>Возможности системы</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <h6>Автоматический расчет деградации</h6>
                                <p>Система учитывает тип зарядки и автоматически рассчитывает износ батареи</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-bar-chart-line"></i>
                            <div>
                                <h6>Графики и прогнозы</h6>
                                <p>Визуализация изменения здоровья батареи и прогноз срока службы</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-bell"></i>
                            <div>
                                <h6>Уведомления</h6>
                                <p>Получайте оповещения о критическом состоянии батарей</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-funnel"></i>
                            <div>
                                <h6>Фильтрация и поиск</h6>
                                <p>Быстро находите нужные устройства по различным параметрам</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-moon-stars"></i>
                            <div>
                                <h6>Темная тема</h6>
                                <p>Удобный интерфейс со светлой и темной темами оформления</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="benefit-item">
                            <i class="bi bi-database"></i>
                            <div>
                                <h6>История зарядок</h6>
                                <p>Полный учет всех циклов зарядки с датами и типами</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
