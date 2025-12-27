<?php
require_once __DIR__ . '/../config/database.php';

$lowHealthDevices = db()->fetchAll(
    "SELECT * FROM devices WHERE battery_health < 80 ORDER BY battery_health ASC"
);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Battery Tracker' ?> | Система учета аккумуляторов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-battery-charging me-2"></i>
                <span>Battery Tracker</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Главная
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : '' ?>" href="devices.php">
                            <i class="bi bi-phone me-1"></i> Устройства
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'charges.php' ? 'active' : '' ?>" href="charges.php">
                            <i class="bi bi-lightning-charge me-1"></i> Зарядки
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : '' ?>" href="analytics.php">
                            <i class="bi bi-graph-up me-1"></i> Аналитика
                        </a>
                    </li>
                </ul>
                
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if (count($lowHealthDevices) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= count($lowHealthDevices) ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropdown">
                        <li><h6 class="dropdown-header">Уведомления</h6></li>
                        <?php if (count($lowHealthDevices) > 0): ?>
                            <?php foreach ($lowHealthDevices as $device): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="device_view.php?id=<?= $device['id'] ?>">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                    <div>
                                        <strong><?= sanitize($device['model']) ?></strong>
                                        <br>
                                        <small class="text-muted">Здоровье батареи: <?= $device['battery_health'] ?>%</small>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted">Нет уведомлений</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container-fluid py-4">
