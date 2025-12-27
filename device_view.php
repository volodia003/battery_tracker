<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('devices.php');
}

$device = db()->fetchOne("SELECT * FROM devices WHERE id = ?", [$id]);

if (!$device) {
    redirect('devices.php');
}

$pageTitle = $device['model'];

$charges = db()->fetchAll(
    "SELECT * FROM charge_cycles WHERE device_id = ? ORDER BY charge_date DESC LIMIT 10",
    [$id]
);

$healthLogs = db()->fetchAll(
    "SELECT * FROM battery_health_logs WHERE device_id = ? ORDER BY logged_date ASC",
    [$id]
);

$chargeStats = db()->fetchOne(
    "SELECT COUNT(*) as total,
            AVG(end_percent - start_percent) as avg_charge,
            AVG(duration_minutes) as avg_duration
     FROM charge_cycles WHERE device_id = ?",
    [$id]
);

$prediction = predictBatteryLife($id);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="devices.php" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 mb-0">
                <i class="bi <?= getDeviceTypeIcon($device['type']) ?> me-2"></i>
                <?= sanitize($device['model']) ?>
            </h1>
            <small class="text-muted"><?= getDeviceTypeLabel($device['type']) ?></small>
        </div>
    </div>
    <div>
        <a href="charge_form.php?device_id=<?= $id ?>" class="btn btn-success me-2">
            <i class="bi bi-lightning-charge me-1"></i>Добавить зарядку
        </a>
        <a href="device_form.php?id=<?= $id ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Редактировать
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">Информация об устройстве</h5>
                
                <div class="text-center mb-4">
                    <div class="health-circle mx-auto <?= getHealthClass($device['battery_health']) ?>">
                        <?= $device['battery_health'] ?>%
                    </div>
                    <p class="mt-2 mb-0">
                        <?php if ($device['battery_health'] >= 80): ?>
                            <span class="badge bg-success">Отличное состояние</span>
                        <?php elseif ($device['battery_health'] >= 50): ?>
                            <span class="badge bg-warning">Требует внимания</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Критическое состояние</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Тип</span>
                        <strong><?= getDeviceTypeLabel($device['type']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Емкость</span>
                        <strong><?= number_format($device['battery_capacity']) ?> mAh</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Дата покупки</span>
                        <strong><?= formatDate($device['purchase_date']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Возраст</span>
                        <strong>
                            <?php
                            $purchaseDate = new DateTime($device['purchase_date']);
                            $now = new DateTime();
                            $diff = $purchaseDate->diff($now);
                            echo $diff->y > 0 ? $diff->y . ' г. ' : '';
                            echo $diff->m . ' мес.';
                            ?>
                        </strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Всего зарядок</span>
                        <strong><?= $chargeStats['total'] ?></strong>
                    </li>
                </ul>
                
                <?php if (!empty($device['notes'])): ?>
                <div class="mt-3">
                    <small class="text-muted">Заметки:</small>
                    <p class="mb-0"><?= nl2br(sanitize($device['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-graph-up-arrow me-2"></i>Прогноз срока службы
                        </h5>
                        <?php if ($prediction): ?>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-primary"><?= $prediction['months'] ?></h3>
                                    <small class="text-muted">месяцев осталось</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-warning"><?= $prediction['drop_per_month'] ?>%</h3>
                                    <small class="text-muted">потеря в месяц</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-info"><?= round($chargeStats['avg_charge']) ?>%</h3>
                                    <small class="text-muted">средний заряд</small>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Недостаточно данных для прогноза. Добавьте больше записей о здоровье батареи.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-heart-pulse me-2"></i>История здоровья батареи
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($healthLogs) > 1): ?>
                        <canvas id="healthHistoryChart" height="200"></canvas>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-graph-up fs-1"></i>
                            <p class="mt-2">Недостаточно данных для построения графика</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-lightning-charge me-2"></i>Последние зарядки
        </h5>
        <a href="charges.php?device_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">Все зарядки</a>
    </div>
    <div class="card-body">
        <?php if (count($charges) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Начало</th>
                        <th>Конец</th>
                        <th>Тип зарядки</th>
                        <th>Длительность</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($charges as $charge): ?>
                    <tr>
                        <td><?= formatDate($charge['charge_date'], 'd.m.Y H:i') ?></td>
                        <td><span class="text-danger"><?= $charge['start_percent'] ?>%</span></td>
                        <td><span class="text-success"><?= $charge['end_percent'] ?>%</span></td>
                        <td>
                            <span class="badge bg-<?= $charge['charge_type'] === 'fast' ? 'warning' : ($charge['charge_type'] === 'wireless' ? 'info' : 'secondary') ?>">
                                <?= getChargeTypeLabel($charge['charge_type']) ?>
                            </span>
                        </td>
                        <td><?= $charge['duration_minutes'] ?> мин</td>
                        <td class="text-end">
                            <a href="charge_form.php?id=<?= $charge['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="deleteCharge(<?= $charge['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-battery fs-1"></i>
            <p class="mt-2">Нет записей о зарядках</p>
            <a href="charge_form.php?device_id=<?= $id ?>" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Добавить первую зарядку
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (count($healthLogs) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('healthHistoryChart').getContext('2d');
    
    const logs = <?= json_encode($healthLogs) ?>;
    const labels = logs.map(l => {
        const date = new Date(l.logged_date);
        return date.toLocaleDateString('ru-RU', { month: 'short', year: 'numeric' });
    });
    const data = logs.map(l => parseFloat(l.health_percent));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Здоровье батареи',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 8
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
                    beginAtZero: false,
                    min: 0,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});

function deleteCharge(id) {
    if (confirm('Вы уверены, что хотите удалить эту запись о зарядке?')) {
        window.location.href = 'charge_delete.php?id=' + id + '&redirect=device_view.php?id=<?= $id ?>';
    }
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
