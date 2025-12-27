<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    db()->delete("DELETE FROM devices WHERE id = ?", [$id]);
}

redirect('devices.php?success=deleted');
