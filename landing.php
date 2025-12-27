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
        
        .task-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .task-section h4 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .task-list {
            list-style: none;
            padding: 0;
        }
        
        .task-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
        }
        
        .task-list li:last-child {
            border-bottom: none;
        }
        
        .task-list li i {
            color: #667eea;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .credentials-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .credentials-box h5 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .credentials-box .cred-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .credentials-box .cred-value {
            font-weight: 700;
            font-family: 'Courier New', monospace;
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
            
            <div class="task-section">
                <h4><i class="bi bi-list-check me-2"></i>Демо-задания в системе</h4>
                <p class="text-muted">После входа в систему вы найдете тестовые данные для ознакомления:</p>
                <ul class="task-list">
                    <li>
                        <i class="bi bi-1-circle-fill"></i>
                        <span><strong>3 устройства:</strong> смартфон, ноутбук и беспроводные наушники</span>
                    </li>
                    <li>
                        <i class="bi bi-2-circle-fill"></i>
                        <span><strong>5 записей о зарядках</strong> для смартфона за разные даты</span>
                    </li>
                    <li>
                        <i class="bi bi-3-circle-fill"></i>
                        <span><strong>Сниженное здоровье батареи</strong> ноутбука (ниже 80%)</span>
                    </li>
                    <li>
                        <i class="bi bi-4-circle-fill"></i>
                        <span><strong>График здоровья батареи</strong> для анализа деградации смартфона</span>
                    </li>
                    <li>
                        <i class="bi bi-5-circle-fill"></i>
                        <span><strong>Фильтр устройств</strong> с здоровьем батареи ниже 80%</span>
                    </li>
                </ul>
            </div>
            
            <div class="credentials-box">
                <h5><i class="bi bi-key me-2"></i>Данные для входа</h5>
                <div class="cred-item">
                    <span>Логин:</span>
                    <span class="cred-value">user123</span>
                </div>
                <div class="cred-item">
                    <span>Пароль:</span>
                    <span class="cred-value">pas123</span>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-rocket-takeoff me-2"></i>Начать работу
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
