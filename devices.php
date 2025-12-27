<?php
$pageTitle = 'Устройства';
require_once 'includes/header.php';

$filterType = $_GET['filter_type'] ?? '';
$filterHealth = $_GET['filter_health'] ?? '';
$search = $_GET['search'] ?? '';

$where = [];
$params = [];

if (!empty($filterType)) {
    $where[] = "type = ?";
    $params[] = $filterType;
}

if ($filterHealth === 'low') {
    $where[] = "battery_health < 80";
} elseif ($filterHealth === 'good') {
    $where[] = "battery_health >= 80";
}

if (!empty($search)) {
    $where[] = "(model LIKE ? OR notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$devices = db()->fetchAll(
    "SELECT d.*, 
            (SELECT COUNT(*) FROM charge_cycles WHERE device_id = d.id) as charge_count
     FROM devices d 
     $whereClause 
     ORDER BY d.battery_health ASC",
    $params
);

$deviceTypes = ['phone', 'laptop', 'screwdriver', 'headphones', 'tablet', 'other'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-phone me-2"></i>Устройства
    </h1>
    <a href="device_form.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Добавить устройство
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Поиск</label>
                <input type="text" name="search" class="form-control" placeholder="Модель или заметки..." 
                       value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Тип устройства</label>
                <select name="filter_type" class="form-select">
                    <option value="">Все типы</option>
                    <?php foreach ($deviceTypes as $type): ?>
                    <option value="<?= $type ?>" <?= $filterType === $type ? 'selected' : '' ?>>
                        <?= getDeviceTypeLabel($type) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Здоровье батареи</label>
                <select name="filter_health" class="form-select">
                    <option value="">Все</option>
                    <option value="good" <?= $filterHealth === 'good' ? 'selected' : '' ?>>Хорошее (≥80%)</option>
                    <option value="low" <?= $filterHealth === 'low' ? 'selected' : '' ?>>Низкое (&lt;80%)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">
                    <i class="bi bi-search me-1"></i>Найти
                </button>
                <a href="devices.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (count($devices) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Устройство</th>
                        <th>Тип</th>
                        <th>Емкость</th>
                        <th>Дата покупки</th>
                        <th>Зарядок</th>
                        <th class="text-center">Здоровье</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="device-icon me-3">
                                    <i class="bi <?= getDeviceTypeIcon($device['type']) ?>"></i>
                                </div>
                                <div>
                                    <a href="device_view.php?id=<?= $device['id'] ?>" class="fw-bold text-decoration-none">
                                        <?= sanitize($device['model']) ?>
                                    </a>
                                    <?php if (!empty($device['notes'])): ?>
                                    <br><small class="text-muted"><?= sanitize(mb_substr($device['notes'], 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= getDeviceTypeLabel($device['type']) ?></td>
                        <td><?= number_format($device['battery_capacity']) ?> mAh</td>
                        <td><?= formatDate($device['purchase_date']) ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= $device['charge_count'] ?></span>
                        </td>
                        <td class="text-center">
                            <div class="health-indicator">
                                <div class="progress" style="height: 8px; width: 80px;">
                                    <div class="progress-bar <?= getHealthBadgeClass($device['battery_health']) ?>" 
                                         style="width: <?= $device['battery_health'] ?>%"></div>
                                </div>
                                <small class="<?= getHealthClass($device['battery_health']) ?>">
                                    <?= $device['battery_health'] ?>%
                                </small>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="device_view.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-primary" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="device_form.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="charge_form.php?device_id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-success" title="Добавить зарядку">
                                    <i class="bi bi-lightning-charge"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteDevice(<?= $device['id'] ?>, '<?= sanitize($device['model']) ?>')" title="Удалить">
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
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="mt-3 text-muted">Устройства не найдены</p>
            <a href="device_form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Добавить первое устройство
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
                <p>Вы уверены, что хотите удалить устройство <strong id="deleteDeviceName"></strong>?</p>
                <p class="text-muted small">Все связанные записи о зарядках также будут удалены.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">Удалить</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteDevice(id, name) {
    document.getElementById('deleteDeviceName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = 'device_delete.php?id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
