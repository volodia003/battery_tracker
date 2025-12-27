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

-- Default user will be created when you run setup_user.php
-- Or you can register a new user through the registration page
-- Default credentials: user123 / pas123

CREATE INDEX idx_devices_type ON devices(type);
CREATE INDEX idx_devices_health ON devices(battery_health);
CREATE INDEX idx_charge_cycles_date ON charge_cycles(charge_date);
CREATE INDEX idx_health_logs_date ON battery_health_logs(logged_date);
