<?php
$pageTitle = 'Главная';
require_once 'includes/header.php';

$totalDevices = db()->fetchOne("SELECT COUNT(*) as count FROM devices")['count'];
$lowHealthCount = db()->fetchOne("SELECT COUNT(*) as count FROM devices WHERE battery_health < 80")['count'];
$totalCharges = db()->fetchOne("SELECT COUNT(*) as count FROM charge_cycles")['count'];
$avgHealth = db()->fetchOne("SELECT AVG(battery_health) as avg FROM devices")['avg'];

$criticalDevices = db()->fetchAll(
    "SELECT * FROM devices WHERE battery_health < 80 ORDER BY battery_health ASC LIMIT 5"
);

$recentCharges = db()->fetchAll(
    "SELECT cc.*, d.model, d.type 
     FROM charge_cycles cc 
     JOIN devices d ON cc.device_id = d.id 
     ORDER BY cc.charge_date DESC 
     LIMIT 5"
);

$allDevices = db()->fetchAll("SELECT id, model, battery_health, type FROM devices ORDER BY battery_health ASC");
?>

<?php if ($lowHealthCount > 0): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
    <div>
        <strong>Внимание!</strong> Обнаружено <?= $lowHealthCount ?> устройств с низким здоровьем батареи (ниже 80%).
        <a href="devices.php?filter_health=low" class="alert-link">Посмотреть список</a>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Всего устройств</h6>
                        <h2 class="mb-0 fw-bold"><?= $totalDevices ?></h2>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-phone"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Требуют внимания</h6>
                        <h2 class="mb-0 fw-bold text-danger"><?= $lowHealthCount ?></h2>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Всего зарядок</h6>
                        <h2 class="mb-0 fw-bold"><?= $totalCharges ?></h2>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Среднее здоровье</h6>
                        <h2 class="mb-0 fw-bold <?= getHealthClass($avgHealth) ?>"><?= round($avgHealth, 1) ?>%</h2>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Устройства с низким здоровьем
                </h5>
                <a href="devices.php?filter_health=low" class="btn btn-sm btn-outline-primary">Все</a>
            </div>
            <div class="card-body">
                <?php if (count($criticalDevices) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Устройство</th>
                                <th>Тип</th>
                                <th class="text-end">Здоровье</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($criticalDevices as $device): ?>
                            <tr>
                                <td>
                                    <a href="device_view.php?id=<?= $device['id'] ?>" class="text-decoration-none">
                                        <i class="bi <?= getDeviceTypeIcon($device['type']) ?> me-2"></i>
                                        <?= sanitize($device['model']) ?>
                                    </a>
                                </td>
                                <td><?= getDeviceTypeLabel($device['type']) ?></td>
                                <td class="text-end">
                                    <span class="badge <?= getHealthBadgeClass($device['battery_health']) ?>">
                                        <?= $device['battery_health'] ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                    <p class="mt-2 mb-0">Все устройства в хорошем состоянии!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-lightning-charge text-success me-2"></i>
                    Последние зарядки
                </h5>
                <a href="charges.php" class="btn btn-sm btn-outline-primary">Все</a>
            </div>
            <div class="card-body">
                <?php if (count($recentCharges) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Устройство</th>
                                <th>Дата</th>
                                <th class="text-end">Заряд</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCharges as $charge): ?>
                            <tr>
                                <td>
                                    <i class="bi <?= getDeviceTypeIcon($charge['type']) ?> me-2"></i>
                                    <?= sanitize($charge['model']) ?>
                                </td>
                                <td><?= formatDate($charge['charge_date'], 'd.m.Y H:i') ?></td>
                                <td class="text-end">
                                    <span class="text-danger"><?= $charge['start_percent'] ?>%</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="text-success"><?= $charge['end_percent'] ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-battery fs-1"></i>
                    <p class="mt-2 mb-0">Нет записей о зарядках</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart text-primary me-2"></i>
                    Здоровье батарей всех устройств
                </h5>
            </div>
            <div class="card-body">
                <canvas id="healthChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('healthChart').getContext('2d');
    
    const devices = <?= json_encode($allDevices) ?>;
    const labels = devices.map(d => d.model);
    const data = devices.map(d => parseFloat(d.battery_health));
    const colors = data.map(h => {
        if (h >= 80) return 'rgba(40, 167, 69, 0.8)';
        if (h >= 50) return 'rgba(255, 193, 7, 0.8)';
        return 'rgba(220, 53, 69, 0.8)';
    });
    
    const theme = document.documentElement.getAttribute('data-theme');
    const isDark = theme === 'dark';
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Здоровье батареи (%)',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.8', '1')),
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: isDark ? '#404040' : '#e0e0e0'
                    },
                    ticks: {
                        color: isDark ? '#b0b0b0' : '#666',
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        color: isDark ? '#404040' : '#e0e0e0'
                    },
                    ticks: {
                        color: isDark ? '#b0b0b0' : '#666'
                    }
                }
            }
        }
    });
    
    // Store chart instance globally
    window.chartInstances = window.chartInstances || [];
    window.chartInstances.push(chart);
});
</script>

<?php require_once 'includes/footer.php'; ?>
