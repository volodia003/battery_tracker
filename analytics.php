<?php
$pageTitle = 'Аналитика';
require_once 'includes/header.php';

$selectedDevice = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;

$devices = db()->fetchAll("SELECT * FROM devices ORDER BY model");

if ($selectedDevice === 0 && count($devices) > 0) {
    $selectedDevice = $devices[0]['id'];
}

$deviceData = null;
$healthLogs = [];
$chargeLogs = [];
$prediction = null;

if ($selectedDevice > 0) {
    $deviceData = db()->fetchOne("SELECT * FROM devices WHERE id = ?", [$selectedDevice]);
    
    $healthLogs = db()->fetchAll(
        "SELECT * FROM battery_health_logs WHERE device_id = ? ORDER BY logged_date ASC",
        [$selectedDevice]
    );
    
    $chargeLogs = db()->fetchAll(
        "SELECT DATE_FORMAT(charge_date, '%Y-%m') as month,
                COUNT(*) as count,
                AVG(end_percent - start_percent) as avg_charge,
                SUM(CASE WHEN charge_type = 'fast' THEN 1 ELSE 0 END) as fast,
                SUM(CASE WHEN charge_type = 'normal' THEN 1 ELSE 0 END) as normal,
                SUM(CASE WHEN charge_type = 'wireless' THEN 1 ELSE 0 END) as wireless
         FROM charge_cycles 
         WHERE device_id = ? 
         GROUP BY DATE_FORMAT(charge_date, '%Y-%m')
         ORDER BY month ASC",
        [$selectedDevice]
    );
    
    $prediction = predictBatteryLife($selectedDevice);
}

$generalStats = db()->fetchOne(
    "SELECT 
        (SELECT COUNT(*) FROM devices) as total_devices,
        (SELECT COUNT(*) FROM devices WHERE battery_health < 80) as low_health,
        (SELECT COUNT(*) FROM charge_cycles) as total_charges,
        (SELECT AVG(battery_health) FROM devices) as avg_health"
);

$deviceTypes = db()->fetchAll(
    "SELECT type, COUNT(*) as count, AVG(battery_health) as avg_health 
     FROM devices GROUP BY type ORDER BY count DESC"
);

$topCharged = db()->fetchAll(
    "SELECT d.*, COUNT(cc.id) as charge_count 
     FROM devices d 
     LEFT JOIN charge_cycles cc ON d.id = cc.device_id 
     GROUP BY d.id 
     ORDER BY charge_count DESC 
     LIMIT 5"
);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-graph-up me-2"></i>Аналитика
    </h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-phone fs-1 text-primary"></i>
                <h3 class="mt-2 mb-0"><?= $generalStats['total_devices'] ?></h3>
                <small class="text-muted">Всего устройств</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                <h3 class="mt-2 mb-0"><?= $generalStats['low_health'] ?></h3>
                <small class="text-muted">Требуют внимания</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-lightning-charge fs-1 text-success"></i>
                <h3 class="mt-2 mb-0"><?= $generalStats['total_charges'] ?></h3>
                <small class="text-muted">Всего зарядок</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-heart-pulse fs-1 text-info"></i>
                <h3 class="mt-2 mb-0"><?= round($generalStats['avg_health'], 1) ?>%</h3>
                <small class="text-muted">Среднее здоровье</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-heart-pulse me-2"></i>График здоровья батареи
                    </h5>
                    <form method="GET" class="d-flex">
                        <select name="device_id" class="form-select form-select-sm" style="width: 200px;" 
                                onchange="this.form.submit()">
                            <?php foreach ($devices as $device): ?>
                            <option value="<?= $device['id'] ?>" <?= $selectedDevice === (int)$device['id'] ? 'selected' : '' ?>>
                                <?= sanitize($device['model']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($healthLogs) > 1): ?>
                <canvas id="healthChart" height="250"></canvas>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-graph-up fs-1"></i>
                    <p class="mt-2">Недостаточно данных для построения графика</p>
                    <p class="small">Добавьте записи об изменении здоровья батареи</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-cpu me-2"></i>Прогноз
                </h5>
            </div>
            <div class="card-body">
                <?php if ($prediction && $deviceData): ?>
                <div class="text-center mb-4">
                    <div class="health-circle mx-auto <?= getHealthClass($deviceData['battery_health']) ?>">
                        <?= $deviceData['battery_health'] ?>%
                    </div>
                    <h6 class="mt-2"><?= sanitize($deviceData['model']) ?></h6>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Осталось примерно</span>
                        <strong class="text-primary"><?= $prediction['months'] ?> мес.</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Потеря в месяц</span>
                        <strong class="text-warning"><?= $prediction['drop_per_month'] ?>%</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Дней до 20%</span>
                        <strong><?= $prediction['days'] ?></strong>
                    </div>
                </div>
                
                <?php if ($deviceData['battery_health'] < 50): ?>
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Рекомендуется замена батареи
                </div>
                <?php elseif ($deviceData['battery_health'] < 80): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Следите за состоянием батареи
                </div>
                <?php else: ?>
                <div class="alert alert-success mt-3 mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    Батарея в хорошем состоянии
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calculator fs-1"></i>
                    <p class="mt-2">Недостаточно данных для прогноза</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Зарядки по месяцам
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($chargeLogs) > 0): ?>
                <canvas id="chargeChart" height="200"></canvas>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-battery fs-1"></i>
                    <p class="mt-2">Нет данных о зарядках</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart me-2"></i>Распределение по типам устройств
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($deviceTypes) > 0): ?>
                <canvas id="typeChart" height="200"></canvas>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-device-hdd fs-1"></i>
                    <p class="mt-2">Нет устройств</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0">
        <h5 class="mb-0">
            <i class="bi bi-trophy me-2"></i>Топ устройств по количеству зарядок
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Устройство</th>
                        <th>Тип</th>
                        <th>Здоровье</th>
                        <th class="text-end">Количество зарядок</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCharged as $index => $device): ?>
                    <tr>
                        <td>
                            <?php if ($index === 0): ?>
                            <i class="bi bi-trophy-fill text-warning"></i>
                            <?php else: ?>
                            <?= $index + 1 ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="device_view.php?id=<?= $device['id'] ?>" class="text-decoration-none">
                                <i class="bi <?= getDeviceTypeIcon($device['type']) ?> me-2"></i>
                                <?= sanitize($device['model']) ?>
                            </a>
                        </td>
                        <td><?= getDeviceTypeLabel($device['type']) ?></td>
                        <td>
                            <span class="badge <?= getHealthBadgeClass($device['battery_health']) ?>">
                                <?= $device['battery_health'] ?>%
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-primary"><?= $device['charge_count'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (count($healthLogs) > 1): ?>
    const healthCtx = document.getElementById('healthChart').getContext('2d');
    const healthLogs = <?= json_encode($healthLogs) ?>;
    
    new Chart(healthCtx, {
        type: 'line',
        data: {
            labels: healthLogs.map(l => {
                const date = new Date(l.logged_date);
                return date.toLocaleDateString('ru-RU', { month: 'short', year: '2-digit' });
            }),
            datasets: [{
                label: 'Здоровье батареи',
                data: healthLogs.map(l => parseFloat(l.health_percent)),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + '%'; }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (count($chargeLogs) > 0): ?>
    const chargeCtx = document.getElementById('chargeChart').getContext('2d');
    const chargeLogs = <?= json_encode($chargeLogs) ?>;
    
    new Chart(chargeCtx, {
        type: 'bar',
        data: {
            labels: chargeLogs.map(l => l.month),
            datasets: [
                {
                    label: 'Быстрая',
                    data: chargeLogs.map(l => parseInt(l.fast)),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)'
                },
                {
                    label: 'Обычная',
                    data: chargeLogs.map(l => parseInt(l.normal)),
                    backgroundColor: 'rgba(108, 117, 125, 0.8)'
                },
                {
                    label: 'Беспроводная',
                    data: chargeLogs.map(l => parseInt(l.wireless)),
                    backgroundColor: 'rgba(23, 162, 184, 0.8)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (count($deviceTypes) > 0): ?>
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    const deviceTypes = <?= json_encode($deviceTypes) ?>;
    const typeLabels = {
        'phone': 'Телефон',
        'laptop': 'Ноутбук',
        'screwdriver': 'Шуруповерт',
        'headphones': 'Наушники',
        'tablet': 'Планшет',
        'other': 'Другое'
    };
    
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: deviceTypes.map(t => typeLabels[t.type] || t.type),
            datasets: [{
                data: deviceTypes.map(t => parseInt(t.count)),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
