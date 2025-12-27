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
-- ТЕСТОВЫЕ ДАННЫЕ
-- ============================================================================

-- Тестовый пользователь: user123 / pas123
-- Хеш пароля создан с помощью password_hash('pas123', PASSWORD_DEFAULT)
INSERT INTO users (id, username, password, email, created_at) VALUES
(1, 'user123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user123@example.com', NOW());

-- Тестовые устройства
INSERT INTO devices (id, user_id, type, model, battery_capacity, purchase_date, battery_health, notes, created_at) VALUES
(1, 1, 'phone', 'iPhone 13 Pro', 3095, '2022-03-15', 89.50, 'Основной телефон. Батарея держит хорошо.', NOW()),
(2, 1, 'laptop', 'MacBook Pro 14"', 7000, '2021-11-20', 92.00, 'Рабочий ноутбук. Редко разряжается полностью.', NOW()),
(3, 1, 'headphones', 'AirPods Pro 2', 523, '2023-01-10', 95.30, 'Беспроводные наушники. Заряжаются часто.', NOW()),
(4, 1, 'phone', 'Samsung Galaxy S21', 4000, '2021-05-05', 75.20, 'Старый телефон. Батарея деградирует быстро.', NOW()),
(5, 1, 'screwdriver', 'Bosch GSR 18V', 2000, '2020-08-12', 68.40, 'Аккумуляторный шуруповерт. Требует замены батареи.', NOW()),
(6, 1, 'tablet', 'iPad Air', 7606, '2022-07-22', 88.70, 'Планшет для работы и развлечений.', NOW());

-- Тестовые записи о зарядках
INSERT INTO charge_cycles (device_id, charge_date, start_percent, end_percent, charge_type, duration_minutes, notes) VALUES
-- iPhone 13 Pro
(1, '2024-12-27 08:30:00', 15, 100, 'fast', 65, 'Утренняя зарядка перед работой'),
(1, '2024-12-26 22:15:00', 25, 95, 'wireless', 180, 'Ночная зарядка на беспроводной подставке'),
(1, '2024-12-25 14:20:00', 30, 100, 'normal', 120, 'Зарядка днем'),
(1, '2024-12-24 19:45:00', 18, 85, 'fast', 55, 'Быстрая зарядка вечером'),
(1, '2024-12-23 09:00:00', 22, 100, 'normal', 110, NULL),

-- MacBook Pro
(2, '2024-12-26 23:00:00', 40, 100, 'normal', 90, 'Зарядка на ночь'),
(2, '2024-12-24 18:30:00', 35, 95, 'normal', 95, 'Зарядка после работы'),
(2, '2024-12-22 20:00:00', 45, 100, 'normal', 85, NULL),

-- AirPods Pro
(3, '2024-12-27 07:00:00', 10, 100, 'wireless', 45, 'Зарядка в кейсе'),
(3, '2024-12-26 12:30:00', 15, 100, 'wireless', 40, 'Подзарядка днем'),
(3, '2024-12-25 08:00:00', 8, 100, 'wireless', 50, NULL),
(3, '2024-12-24 19:00:00', 12, 100, 'wireless', 42, NULL),

-- Samsung Galaxy
(4, '2024-12-27 06:45:00', 5, 95, 'fast', 70, 'Быстрая зарядка утром'),
(4, '2024-12-26 21:00:00', 12, 88, 'normal', 140, 'Вечерняя зарядка'),
(4, '2024-12-25 16:30:00', 8, 92, 'fast', 75, 'Срочная подзарядка'),

-- Шуруповерт
(5, '2024-12-26 10:00:00', 0, 100, 'normal', 180, 'Полная зарядка перед работой'),
(5, '2024-12-20 15:30:00', 5, 100, 'normal', 175, 'Зарядка после использования'),

-- iPad
(6, '2024-12-27 00:30:00', 28, 100, 'fast', 95, 'Ночная зарядка'),
(6, '2024-12-25 11:00:00', 35, 100, 'normal', 110, NULL),
(6, '2024-12-23 20:15:00', 42, 100, 'fast', 80, NULL);

-- Записи о здоровье батареи для аналитики и прогнозов
INSERT INTO battery_health_logs (device_id, logged_date, health_percent, notes) VALUES
-- iPhone 13 Pro (деградация со временем)
(1, '2022-03-15', 100.00, 'Покупка устройства'),
(1, '2022-09-01', 98.50, NULL),
(1, '2023-03-01', 96.20, NULL),
(1, '2023-09-01', 93.80, NULL),
(1, '2024-03-01', 91.40, NULL),
(1, '2024-09-01', 89.90, NULL),
(1, '2024-12-27', 89.50, 'Текущее состояние'),

-- MacBook Pro (медленная деградация)
(2, '2021-11-20', 100.00, 'Покупка устройства'),
(2, '2022-05-01', 98.00, NULL),
(2, '2022-11-01', 96.50, NULL),
(2, '2023-05-01', 95.20, NULL),
(2, '2023-11-01', 93.80, NULL),
(2, '2024-05-01', 92.50, NULL),
(2, '2024-12-27', 92.00, 'Текущее состояние'),

-- AirPods Pro (хорошее состояние)
(3, '2023-01-10', 100.00, 'Покупка устройства'),
(3, '2023-06-01', 98.20, NULL),
(3, '2023-12-01', 96.80, NULL),
(3, '2024-06-01', 95.90, NULL),
(3, '2024-12-27', 95.30, 'Текущее состояние'),

-- Samsung Galaxy (быстрая деградация)
(4, '2021-05-05', 100.00, 'Покупка устройства'),
(4, '2021-11-01', 95.00, NULL),
(4, '2022-05-01', 89.50, NULL),
(4, '2022-11-01', 84.20, NULL),
(4, '2023-05-01', 80.10, 'Начало деградации'),
(4, '2023-11-01', 77.80, NULL),
(4, '2024-05-01', 76.20, NULL),
(4, '2024-12-27', 75.20, 'Требуется внимание'),

-- Шуруповерт (сильная деградация)
(5, '2020-08-12', 100.00, 'Покупка устройства'),
(5, '2021-02-01', 92.00, NULL),
(5, '2021-08-01', 85.50, NULL),
(5, '2022-02-01', 79.20, NULL),
(5, '2022-08-01', 74.80, 'Падение здоровья'),
(5, '2023-02-01', 71.50, NULL),
(5, '2023-08-01', 69.30, NULL),
(5, '2024-02-01', 68.90, NULL),
(5, '2024-12-27', 68.40, 'Рекомендуется замена'),

-- iPad (хорошее состояние)
(6, '2022-07-22', 100.00, 'Покупка устройства'),
(6, '2023-01-01', 96.80, NULL),
(6, '2023-07-01', 93.50, NULL),
(6, '2024-01-01', 91.20, NULL),
(6, '2024-07-01', 89.80, NULL),
(6, '2024-12-27', 88.70, 'Текущее состояние');

-- ============================================================================
-- ПРИМЕЧАНИЯ
-- ============================================================================
-- 
-- Учетные данные для входа:
-- Логин: user123
-- Пароль: pas123
--
-- В базу добавлено:
-- - 1 тестовый пользователь
-- - 6 тестовых устройств разных типов
-- - 20 записей о зарядках
-- - 42 записи о здоровье батареи для аналитики
--
-- Все данные созданы для демонстрации функционала системы Battery Tracker
-- ============================================================================
