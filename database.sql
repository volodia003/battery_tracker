CREATE DATABASE IF NOT EXISTS battery_tracker_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE battery_tracker_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT 1,
    type ENUM('phone', 'laptop', 'screwdriver', 'headphones', 'tablet', 'other') NOT NULL,
    model VARCHAR(150) NOT NULL,
    battery_capacity INT NOT NULL,
    purchase_date DATE NOT NULL,
    battery_health DECIMAL(5,2) DEFAULT 100.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS charge_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    charge_date DATETIME NOT NULL,
    start_percent TINYINT UNSIGNED NOT NULL,
    end_percent TINYINT UNSIGNED NOT NULL,
    charge_type ENUM('fast', 'normal', 'wireless') NOT NULL DEFAULT 'normal',
    duration_minutes INT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS battery_health_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    logged_date DATE NOT NULL,
    health_percent DECIMAL(5,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Indexes for better performance
CREATE INDEX idx_devices_type ON devices(type);
CREATE INDEX idx_devices_health ON devices(battery_health);
CREATE INDEX idx_charge_cycles_date ON charge_cycles(charge_date);
CREATE INDEX idx_health_logs_date ON battery_health_logs(logged_date);

-- ============================================================================
-- ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ
-- ============================================================================

-- Предустановленный тестовый пользователь: user123 / pas123
-- Хеш пароля создан с помощью password_hash('pas123', PASSWORD_DEFAULT)
INSERT INTO users (id, username, password, email, created_at) VALUES
(1, 'user123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user123@example.com', NOW());

-- ============================================================================
-- СТАРТОВЫЕ ДАННЫЕ ДЛЯ ДЕМО-ЗАДАНИЙ
-- ============================================================================

-- ЗАДАНИЕ 1: Добавлено 3 устройства
INSERT INTO devices (id, user_id, type, model, battery_capacity, purchase_date, battery_health, notes, created_at) VALUES
(1, 1, 'phone', 'Samsung Galaxy S21', 4000, '2022-06-15', 92.50, 'Смартфон для повседневного использования', NOW()),
(2, 1, 'laptop', 'ASUS ROG Strix', 5000, '2021-03-20', 75.30, 'Игровой ноутбук, батарея деградирует', NOW()),
(3, 1, 'headphones', 'Sony WH-1000XM5', 520, '2023-08-10', 98.00, 'Беспроводные наушники с шумоподавлением', NOW());

-- ЗАДАНИЕ 2: 5 записей о зарядках для смартфона (Samsung Galaxy S21)
INSERT INTO charge_cycles (device_id, charge_date, start_percent, end_percent, charge_type, duration_minutes, notes) VALUES
(1, '2024-12-20 08:30:00', 15, 100, 'fast', 70, 'Утренняя быстрая зарядка'),
(1, '2024-12-22 14:45:00', 25, 95, 'normal', 120, 'Зарядка днем в офисе'),
(1, '2024-12-23 21:00:00', 10, 100, 'wireless', 180, 'Ночная беспроводная зарядка'),
(1, '2024-12-25 11:20:00', 30, 85, 'fast', 55, 'Быстрая подзарядка перед выходом'),
(1, '2024-12-27 07:15:00', 18, 100, 'normal', 110, 'Стандартная зарядка утром');

-- ЗАДАНИЕ 3: Здоровье батареи ноутбука уменьшено до 75.30% (ниже 80%)
-- (уже установлено в INSERT выше)

-- ЗАДАНИЕ 4: История здоровья батареи для смартфона (для графика)
INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) VALUES
(1, '2022-06-15', 100.00, 'Покупка устройства'),
(1, '2022-09-15', 98.50, 'Проверка через 3 месяца'),
(1, '2022-12-15', 97.20, 'Проверка через 6 месяцев'),
(1, '2023-03-15', 96.00, 'Небольшая деградация'),
(1, '2023-06-15', 95.10, 'Год использования'),
(1, '2023-09-15', 94.30, 'Продолжение деградации'),
(1, '2023-12-15', 93.50, 'Полтора года использования'),
(1, '2024-03-15', 93.00, 'Стабильное состояние'),
(1, '2024-06-15', 92.80, 'Два года использования'),
(1, '2024-09-15', 92.60, 'Замедление деградации'),
(1, '2024-12-27', 92.50, 'Текущее состояние');

-- Дополнительные записи для ноутбука (для демонстрации устройства с низким здоровьем)
INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) VALUES
(2, '2021-03-20', 100.00, 'Покупка ноутбука'),
(2, '2021-09-20', 95.00, 'Первые признаки износа'),
(2, '2022-03-20', 90.00, 'Год использования'),
(2, '2022-09-20', 85.00, 'Ускоренная деградация'),
(2, '2023-03-20', 80.00, 'Два года - падение ниже 80%'),
(2, '2023-09-20', 78.00, 'Требуется внимание'),
(2, '2024-03-20', 76.50, 'Продолжение снижения'),
(2, '2024-12-27', 75.30, 'Текущее состояние - требуется замена');

-- Записи для наушников
INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) VALUES
(3, '2023-08-10', 100.00, 'Покупка наушников'),
(3, '2023-11-10', 99.20, 'Отличное состояние'),
(3, '2024-02-10', 98.80, 'Минимальная деградация'),
(3, '2024-05-10', 98.50, 'Стабильное здоровье'),
(3, '2024-08-10', 98.20, 'Год использования'),
(3, '2024-12-27', 98.00, 'Текущее состояние');

-- ============================================================================
-- ПРИМЕЧАНИЯ
-- ============================================================================
-- 
-- Учетные данные для входа:
-- Логин: user123
-- Пароль: pas123
--
-- ДЕМО-ЗАДАНИЯ:
-- 1. ✓ Добавлены 3 устройства: смартфон, ноутбук, беспроводные наушники
-- 2. ✓ Для смартфона добавлено 5 записей о зарядках за разные даты
-- 3. ✓ Здоровье батареи ноутбука снижено до 75.30% (ниже 80%)
-- 4. ✓ Добавлена история для построения графика здоровья смартфона
-- 5. ✓ Ноутбук появится при фильтрации устройств с здоровьем < 80%
--
-- Для просмотра графика: Аналитика → выберите Samsung Galaxy S21
-- Для фильтрации: Устройства → Здоровье батареи → Низкое (<80%)
-- ============================================================================
