<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'battery_tracker_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    public function update($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }
}

function db() {
    return Database::getInstance();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function getHealthClass($health) {
    if ($health >= 80) return 'health-good';
    if ($health >= 50) return 'health-warning';
    return 'health-danger';
}

function getHealthBadgeClass($health) {
    if ($health >= 80) return 'bg-success';
    if ($health >= 50) return 'bg-warning';
    return 'bg-danger';
}

function getDeviceTypeLabel($type) {
    $types = [
        'phone' => 'Телефон',
        'laptop' => 'Ноутбук',
        'screwdriver' => 'Шуруповерт',
        'headphones' => 'Наушники',
        'tablet' => 'Планшет',
        'other' => 'Другое'
    ];
    return $types[$type] ?? $type;
}

function getDeviceTypeIcon($type) {
    $icons = [
        'phone' => 'bi-phone',
        'laptop' => 'bi-laptop',
        'screwdriver' => 'bi-tools',
        'headphones' => 'bi-headphones',
        'tablet' => 'bi-tablet',
        'other' => 'bi-device-hdd'
    ];
    return $icons[$type] ?? 'bi-device-hdd';
}

function getChargeTypeLabel($type) {
    $types = [
        'fast' => 'Быстрая',
        'normal' => 'Обычная',
        'wireless' => 'Беспроводная'
    ];
    return $types[$type] ?? $type;
}

function predictBatteryLife($deviceId) {
    $db = db();
    
    $logs = $db->fetchAll(
        "SELECT * FROM battery_health_logs WHERE device_id = ? ORDER BY logged_date ASC",
        [$deviceId]
    );
    
    if (count($logs) < 2) {
        return null;
    }
    
    $firstLog = $logs[0];
    $lastLog = $logs[count($logs) - 1];
    
    $daysDiff = (strtotime($lastLog['logged_date']) - strtotime($firstLog['logged_date'])) / 86400;
    $healthDrop = $firstLog['health_percent'] - $lastLog['health_percent'];
    
    if ($daysDiff <= 0 || $healthDrop <= 0) {
        return null;
    }
    
    $dropPerDay = $healthDrop / $daysDiff;
    $remainingHealth = $lastLog['health_percent'] - 20;
    
    if ($dropPerDay <= 0) {
        return null;
    }
    
    $daysRemaining = $remainingHealth / $dropPerDay;
    $monthsRemaining = round($daysRemaining / 30);
    
    return [
        'days' => round($daysRemaining),
        'months' => $monthsRemaining,
        'drop_per_month' => round($dropPerDay * 30, 2)
    ];
}
