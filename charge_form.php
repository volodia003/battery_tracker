<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$isEdit = $id > 0;
$charge = null;

if ($isEdit) {
    $charge = db()->fetchOne("SELECT * FROM charge_cycles WHERE id = ?", [$id]);
    if (!$charge) {
        redirect('charges.php');
    }
    $deviceId = $charge['device_id'];
}

$pageTitle = $isEdit ? 'Редактирование записи о зарядке' : 'Добавление зарядки';

$devices = db()->fetchAll("SELECT id, model, type FROM devices ORDER BY model");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_id = (int)($_POST['device_id'] ?? 0);
    $charge_date = $_POST['charge_date'] ?? '';
    $charge_time = $_POST['charge_time'] ?? '12:00';
    $start_percent = (int)($_POST['start_percent'] ?? 0);
    $end_percent = (int)($_POST['end_percent'] ?? 0);
    $charge_type = $_POST['charge_type'] ?? 'normal';
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    if ($device_id <= 0) $errors[] = 'Выберите устройство';
    if (empty($charge_date)) $errors[] = 'Выберите дату зарядки';
    if ($start_percent < 0 || $start_percent > 100) $errors[] = 'Начальный процент должен быть от 0 до 100';
    if ($end_percent < 0 || $end_percent > 100) $errors[] = 'Конечный процент должен быть от 0 до 100';
    if ($start_percent >= $end_percent) $errors[] = 'Конечный процент должен быть больше начального';
    if ($duration_minutes <= 0) $errors[] = 'Введите длительность зарядки';
    
    if (empty($errors)) {
        $fullDate = $charge_date . ' ' . $charge_time . ':00';
        
        if ($isEdit) {
            db()->update(
                "UPDATE charge_cycles SET device_id = ?, charge_date = ?, start_percent = ?, 
                        end_percent = ?, charge_type = ?, duration_minutes = ?, notes = ? WHERE id = ?",
                [$device_id, $fullDate, $start_percent, $end_percent, $charge_type, $duration_minutes, $notes, $id]
            );
        } else {
            db()->insert(
                "INSERT INTO charge_cycles (device_id, charge_date, start_percent, end_percent, charge_type, duration_minutes, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$device_id, $fullDate, $start_percent, $end_percent, $charge_type, $duration_minutes, $notes]
            );
            
            $degradationCoefficients = [
                'normal' => 0.05,
                'fast' => 0.08,
                'wireless' => 0.06
            ];
            
            $cyclePercent = ($end_percent - $start_percent) / 100;
            $baseCoeff = $degradationCoefficients[$charge_type] ?? 0.05;
            $degradation = round($cyclePercent * $baseCoeff, 3);
            
            $device = db()->fetchOne("SELECT battery_health FROM devices WHERE id = ?", [$device_id]);
            $currentHealth = (float)$device['battery_health'];
            $newHealth = max(0, $currentHealth - $degradation);
            
            db()->update(
                "UPDATE devices SET battery_health = ? WHERE id = ?",
                [$newHealth, $device_id]
            );
            
            $lastLog = db()->fetchOne(
                "SELECT health_percent FROM battery_health_logs WHERE device_id = ? ORDER BY logged_date DESC LIMIT 1",
                [$device_id]
            );
            
            if (!$lastLog || abs($lastLog['health_percent'] - $newHealth) >= 0.1) {
                db()->insert(
                    "INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) 
                     VALUES (?, CURDATE(), ?, ?)",
                    [$device_id, $newHealth, "Износ от зарядки: -" . number_format($degradation, 3) . "%"]
                );
            }
        }
        
        redirect('charges.php?success=' . ($isEdit ? 'updated' : 'added'));
    }
}

$formData = [
    'device_id' => $charge['device_id'] ?? $deviceId ?? $_POST['device_id'] ?? 0,
    'charge_date' => isset($charge['charge_date']) ? date('Y-m-d', strtotime($charge['charge_date'])) : ($_POST['charge_date'] ?? date('Y-m-d')),
    'charge_time' => isset($charge['charge_date']) ? date('H:i', strtotime($charge['charge_date'])) : ($_POST['charge_time'] ?? date('H:i')),
    'start_percent' => $charge['start_percent'] ?? $_POST['start_percent'] ?? 20,
    'end_percent' => $charge['end_percent'] ?? $_POST['end_percent'] ?? 100,
    'charge_type' => $charge['charge_type'] ?? $_POST['charge_type'] ?? 'normal',
    'duration_minutes' => $charge['duration_minutes'] ?? $_POST['duration_minutes'] ?? 60,
    'notes' => $charge['notes'] ?? $_POST['notes'] ?? ''
];

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="mb-0">
                    <i class="bi bi-<?= $isEdit ? 'pencil' : 'lightning-charge' ?> me-2"></i>
                    <?= $pageTitle ?>
                </h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Устройство <span class="text-danger">*</span></label>
                            <select name="device_id" class="form-select" required>
                                <option value="">Выберите устройство...</option>
                                <?php foreach ($devices as $device): ?>
                                <option value="<?= $device['id'] ?>" 
                                    <?= (int)$formData['device_id'] === (int)$device['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($device['model']) ?> (<?= getDeviceTypeLabel($device['type']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Дата <span class="text-danger">*</span></label>
                            <input type="date" name="charge_date" class="form-control" required
                                   value="<?= $formData['charge_date'] ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Время</label>
                            <input type="time" name="charge_time" class="form-control"
                                   value="<?= $formData['charge_time'] ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Уровень заряда</label>
                            <div class="charge-range-container p-4 bg-light rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label text-muted small">Начальный процент</label>
                                        <div class="input-group">
                                            <input type="number" name="start_percent" class="form-control" 
                                                   min="0" max="99" required
                                                   value="<?= $formData['start_percent'] ?>" id="startPercent">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <input type="range" class="form-range mt-2" min="0" max="99" 
                                               id="startRange" value="<?= $formData['start_percent'] ?>">
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <i class="bi bi-arrow-right fs-3 text-success"></i>
                                        <div id="chargeGain" class="fw-bold text-primary">
                                            +<?= $formData['end_percent'] - $formData['start_percent'] ?>%
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label text-muted small">Конечный процент</label>
                                        <div class="input-group">
                                            <input type="number" name="end_percent" class="form-control" 
                                                   min="1" max="100" required
                                                   value="<?= $formData['end_percent'] ?>" id="endPercent">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <input type="range" class="form-range mt-2" min="1" max="100" 
                                               id="endRange" value="<?= $formData['end_percent'] ?>">
                                    </div>
                                </div>
                                
                                <div class="battery-visual mt-4">
                                    <div class="battery-body">
                                        <div class="battery-level" id="batteryLevel" 
                                             style="width: <?= $formData['end_percent'] ?>%"></div>
                                        <div class="battery-start" id="batteryStart" 
                                             style="left: <?= $formData['start_percent'] ?>%"></div>
                                    </div>
                                    <div class="battery-tip"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Тип зарядки <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="charge_type" id="typeNormal" 
                                       value="normal" <?= $formData['charge_type'] === 'normal' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="typeNormal">
                                    <i class="bi bi-plug me-1"></i>Обычная
                                </label>
                                
                                <input type="radio" class="btn-check" name="charge_type" id="typeFast" 
                                       value="fast" <?= $formData['charge_type'] === 'fast' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-warning" for="typeFast">
                                    <i class="bi bi-lightning me-1"></i>Быстрая
                                </label>
                                
                                <input type="radio" class="btn-check" name="charge_type" id="typeWireless" 
                                       value="wireless" <?= $formData['charge_type'] === 'wireless' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-info" for="typeWireless">
                                    <i class="bi bi-broadcast me-1"></i>Беспроводная
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Длительность (минуты) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="duration_minutes" class="form-control" 
                                       min="1" max="1440" required
                                       value="<?= $formData['duration_minutes'] ?>">
                                <span class="input-group-text">мин</span>
                            </div>
                            <div class="form-text">
                                ≈ <?= floor($formData['duration_minutes'] / 60) ?>ч <?= $formData['duration_minutes'] % 60 ?>мин
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Заметки</label>
                            <textarea name="notes" class="form-control" rows="2" 
                                      placeholder="Дополнительная информация..."><?= sanitize($formData['notes']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= $deviceId ? "device_view.php?id=$deviceId" : 'charges.php' ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Назад
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $isEdit ? 'Сохранить изменения' : 'Добавить зарядку' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startPercent = document.getElementById('startPercent');
    const endPercent = document.getElementById('endPercent');
    const startRange = document.getElementById('startRange');
    const endRange = document.getElementById('endRange');
    const chargeGain = document.getElementById('chargeGain');
    const batteryLevel = document.getElementById('batteryLevel');
    const batteryStart = document.getElementById('batteryStart');
    
    function updateVisualization() {
        const start = parseInt(startPercent.value) || 0;
        const end = parseInt(endPercent.value) || 0;
        const gain = end - start;
        
        chargeGain.textContent = (gain >= 0 ? '+' : '') + gain + '%';
        chargeGain.className = gain > 0 ? 'fw-bold text-primary' : 'fw-bold text-danger';
        
        batteryLevel.style.width = end + '%';
        batteryStart.style.left = start + '%';
        
        if (end >= 80) {
            batteryLevel.style.background = 'linear-gradient(90deg, #28a745, #20c997)';
        } else if (end >= 50) {
            batteryLevel.style.background = 'linear-gradient(90deg, #ffc107, #fd7e14)';
        } else {
            batteryLevel.style.background = 'linear-gradient(90deg, #dc3545, #fd7e14)';
        }
    }
    
    startPercent.addEventListener('input', function() {
        startRange.value = this.value;
        updateVisualization();
    });
    
    endPercent.addEventListener('input', function() {
        endRange.value = this.value;
        updateVisualization();
    });
    
    startRange.addEventListener('input', function() {
        startPercent.value = this.value;
        updateVisualization();
    });
    
    endRange.addEventListener('input', function() {
        endPercent.value = this.value;
        updateVisualization();
    });
    
    updateVisualization();
});
</script>

<?php require_once 'includes/footer.php'; ?>
