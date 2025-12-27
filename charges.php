<?php
$pageTitle = 'Журнал зарядок';
require_once 'includes/header.php';

$filterDevice = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$filterType = $_GET['charge_type'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

$where = [];
$params = [];

if ($filterDevice > 0) {
    $where[] = "cc.device_id = ?";
    $params[] = $filterDevice;
}

if (!empty($filterType)) {
    $where[] = "cc.charge_type = ?";
    $params[] = $filterType;
}

if (!empty($filterDateFrom)) {
    $where[] = "DATE(cc.charge_date) >= ?";
    $params[] = $filterDateFrom;
}

if (!empty($filterDateTo)) {
    $where[] = "DATE(cc.charge_date) <= ?";
    $params[] = $filterDateTo;
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$charges = db()->fetchAll(
    "SELECT cc.*, d.model, d.type, d.id as device_id
     FROM charge_cycles cc 
     JOIN devices d ON cc.device_id = d.id 
     $whereClause 
     ORDER BY cc.charge_date DESC",
    $params
);

$devices = db()->fetchAll("SELECT id, model FROM devices ORDER BY model");

$stats = db()->fetchOne(
    "SELECT 
        COUNT(*) as total,
        AVG(end_percent - start_percent) as avg_charge,
        AVG(duration_minutes) as avg_duration,
        SUM(CASE WHEN charge_type = 'fast' THEN 1 ELSE 0 END) as fast_count,
        SUM(CASE WHEN charge_type = 'wireless' THEN 1 ELSE 0 END) as wireless_count
     FROM charge_cycles cc
     $whereClause",
    $params
);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-lightning-charge me-2"></i>Журнал зарядок
    </h1>
    <a href="charge_form.php" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Добавить зарядку
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['total'] ?></h4>
                <small class="text-muted">Всего записей</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= round($stats['avg_charge']) ?>%</h4>
                <small class="text-muted">Средний заряд</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= round($stats['avg_duration']) ?> мин</h4>
                <small class="text-muted">Среднее время</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['fast_count'] ?></h4>
                <small class="text-muted">Быстрых зарядок</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Устройство</label>
                <select name="device_id" class="form-select">
                    <option value="">Все устройства</option>
                    <?php foreach ($devices as $device): ?>
                    <option value="<?= $device['id'] ?>" <?= $filterDevice === (int)$device['id'] ? 'selected' : '' ?>>
                        <?= sanitize($device['model']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Тип зарядки</label>
                <select name="charge_type" class="form-select">
                    <option value="">Все</option>
                    <option value="fast" <?= $filterType === 'fast' ? 'selected' : '' ?>>Быстрая</option>
                    <option value="normal" <?= $filterType === 'normal' ? 'selected' : '' ?>>Обычная</option>
                    <option value="wireless" <?= $filterType === 'wireless' ? 'selected' : '' ?>>Беспроводная</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Дата от</label>
                <input type="date" name="date_from" class="form-control" value="<?= $filterDateFrom ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Дата до</label>
                <input type="date" name="date_to" class="form-control" value="<?= $filterDateTo ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">
                    <i class="bi bi-search me-1"></i>Найти
                </button>
                <a href="charges.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (count($charges) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Устройство</th>
                        <th>Дата и время</th>
                        <th class="text-center">Начало</th>
                        <th class="text-center">Конец</th>
                        <th class="text-center">Заряжено</th>
                        <th>Тип</th>
                        <th>Время</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($charges as $charge): ?>
                    <tr>
                        <td>
                            <a href="device_view.php?id=<?= $charge['device_id'] ?>" class="text-decoration-none">
                                <i class="bi <?= getDeviceTypeIcon($charge['type']) ?> me-2"></i>
                                <?= sanitize($charge['model']) ?>
                            </a>
                        </td>
                        <td><?= formatDate($charge['charge_date'], 'd.m.Y H:i') ?></td>
                        <td class="text-center">
                            <span class="badge bg-danger"><?= $charge['start_percent'] ?>%</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= $charge['end_percent'] ?>%</span>
                        </td>
                        <td class="text-center">
                            <strong class="text-primary">+<?= $charge['end_percent'] - $charge['start_percent'] ?>%</strong>
                        </td>
                        <td>
                            <?php
                            $typeClass = 'secondary';
                            $typeIcon = 'plug';
                            if ($charge['charge_type'] === 'fast') {
                                $typeClass = 'warning';
                                $typeIcon = 'lightning';
                            } elseif ($charge['charge_type'] === 'wireless') {
                                $typeClass = 'info';
                                $typeIcon = 'broadcast';
                            }
                            ?>
                            <span class="badge bg-<?= $typeClass ?>">
                                <i class="bi bi-<?= $typeIcon ?> me-1"></i>
                                <?= getChargeTypeLabel($charge['charge_type']) ?>
                            </span>
                        </td>
                        <td><?= $charge['duration_minutes'] ?> мин</td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="charge_form.php?id=<?= $charge['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteCharge(<?= $charge['id'] ?>)" title="Удалить">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-battery fs-1 text-muted"></i>
            <p class="mt-3 text-muted">Записи о зарядках не найдены</p>
            <a href="charge_form.php" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Добавить первую зарядку
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту запись о зарядке?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">Удалить</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCharge(id) {
    document.getElementById('deleteConfirmBtn').href = 'charge_delete.php?id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
