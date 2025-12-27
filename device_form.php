<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$device = null;

if ($isEdit) {
    $device = db()->fetchOne("SELECT * FROM devices WHERE id = ?", [$id]);
    if (!$device) {
        redirect('devices.php');
    }
}

$pageTitle = $isEdit ? 'Редактирование устройства' : 'Добавление устройства';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $model = trim($_POST['model'] ?? '');
    $battery_capacity = (int)($_POST['battery_capacity'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?? '';
    $battery_health = (float)($_POST['battery_health'] ?? 100);
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    if (empty($type)) $errors[] = 'Выберите тип устройства';
    if (empty($model)) $errors[] = 'Введите модель устройства';
    if ($battery_capacity <= 0) $errors[] = 'Введите корректную емкость аккумулятора';
    if (empty($purchase_date)) $errors[] = 'Выберите дату покупки';
    if ($battery_health < 0 || $battery_health > 100) $errors[] = 'Здоровье батареи должно быть от 0 до 100';
    
    if (empty($errors)) {
        if ($isEdit) {
            if ($device['battery_health'] != $battery_health) {
                db()->insert(
                    "INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) 
                     VALUES (?, CURDATE(), ?, ?)",
                    [$id, $battery_health, 'Ручное изменение']
                );
            }
            
            db()->update(
                "UPDATE devices SET type = ?, model = ?, battery_capacity = ?, 
                        purchase_date = ?, battery_health = ?, notes = ? WHERE id = ?",
                [$type, $model, $battery_capacity, $purchase_date, $battery_health, $notes, $id]
            );
            $message = 'Устройство успешно обновлено';
        } else {
            $newId = db()->insert(
                "INSERT INTO devices (user_id, type, model, battery_capacity, purchase_date, battery_health, notes) 
                 VALUES (1, ?, ?, ?, ?, ?, ?)",
                [$type, $model, $battery_capacity, $purchase_date, $battery_health, $notes]
            );
            
            db()->insert(
                "INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) 
                 VALUES (?, ?, ?, ?)",
                [$newId, $purchase_date, $battery_health, 'Первоначальная запись']
            );
            
            redirect('devices.php?success=added');
        }
        
        redirect('devices.php?success=' . ($isEdit ? 'updated' : 'added'));
    }
}

$deviceTypes = [
    'phone' => 'Телефон',
    'laptop' => 'Ноутбук',
    'screwdriver' => 'Шуруповерт',
    'headphones' => 'Наушники',
    'tablet' => 'Планшет',
    'other' => 'Другое'
];

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="mb-0">
                    <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-lg' ?> me-2"></i>
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
                            <label class="form-label">Тип устройства <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Выберите тип...</option>
                                <?php foreach ($deviceTypes as $value => $label): ?>
                                <option value="<?= $value ?>" 
                                    <?= ($device['type'] ?? $_POST['type'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Модель <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control" required
                                   value="<?= sanitize($device['model'] ?? $_POST['model'] ?? '') ?>"
                                   placeholder="Например: iPhone 14 Pro">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Емкость аккумулятора (mAh) <span class="text-danger">*</span></label>
                            <input type="number" name="battery_capacity" class="form-control" required min="1"
                                   value="<?= (int)($device['battery_capacity'] ?? $_POST['battery_capacity'] ?? '') ?>"
                                   placeholder="Например: 3200">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Дата покупки <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" required
                                   value="<?= $device['purchase_date'] ?? $_POST['purchase_date'] ?? '' ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Здоровье батареи (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="battery_health" class="form-control" required
                                       min="0" max="100" step="0.1"
                                       value="<?= (float)($device['battery_health'] ?? $_POST['battery_health'] ?? 100) ?>"
                                       id="healthInput">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="mt-2">
                                <input type="range" class="form-range" min="0" max="100" step="0.5" 
                                       id="healthRange" 
                                       value="<?= (float)($device['battery_health'] ?? $_POST['battery_health'] ?? 100) ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Индикатор здоровья</label>
                            <div class="health-preview p-3 rounded" id="healthPreview">
                                <div class="d-flex align-items-center">
                                    <div class="health-indicator-large me-3" id="healthIndicator"></div>
                                    <div>
                                        <strong id="healthStatus">Отличное</strong>
                                        <br><small class="text-muted" id="healthDescription">Батарея в хорошем состоянии</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Заметки</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Дополнительная информация об устройстве..."><?= sanitize($device['notes'] ?? $_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="devices.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $isEdit ? 'Сохранить изменения' : 'Добавить устройство' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const healthInput = document.getElementById('healthInput');
    const healthRange = document.getElementById('healthRange');
    const healthIndicator = document.getElementById('healthIndicator');
    const healthStatus = document.getElementById('healthStatus');
    const healthDescription = document.getElementById('healthDescription');
    const healthPreview = document.getElementById('healthPreview');
    
    function updateHealthPreview(value) {
        value = parseFloat(value);
        
        healthIndicator.style.width = '60px';
        healthIndicator.style.height = '60px';
        healthIndicator.style.borderRadius = '50%';
        healthIndicator.style.display = 'flex';
        healthIndicator.style.alignItems = 'center';
        healthIndicator.style.justifyContent = 'center';
        healthIndicator.style.fontWeight = 'bold';
        healthIndicator.style.fontSize = '14px';
        healthIndicator.textContent = value.toFixed(1) + '%';
        
        if (value >= 80) {
            healthIndicator.style.backgroundColor = '#d4edda';
            healthIndicator.style.color = '#155724';
            healthPreview.style.backgroundColor = '#d4edda';
            healthStatus.textContent = 'Отличное';
            healthDescription.textContent = 'Батарея в хорошем состоянии';
        } else if (value >= 50) {
            healthIndicator.style.backgroundColor = '#fff3cd';
            healthIndicator.style.color = '#856404';
            healthPreview.style.backgroundColor = '#fff3cd';
            healthStatus.textContent = 'Удовлетворительное';
            healthDescription.textContent = 'Рекомендуется следить за состоянием';
        } else {
            healthIndicator.style.backgroundColor = '#f8d7da';
            healthIndicator.style.color = '#721c24';
            healthPreview.style.backgroundColor = '#f8d7da';
            healthStatus.textContent = 'Критическое';
            healthDescription.textContent = 'Требуется замена батареи';
        }
    }
    
    healthInput.addEventListener('input', function() {
        healthRange.value = this.value;
        updateHealthPreview(this.value);
    });
    
    healthRange.addEventListener('input', function() {
        healthInput.value = this.value;
        updateHealthPreview(this.value);
    });
    
    updateHealthPreview(healthInput.value);
});
</script>

<?php require_once 'includes/footer.php'; ?>
