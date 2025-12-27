<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = $_GET['redirect'] ?? 'charges.php';

if ($id > 0) {
    db()->delete("DELETE FROM charge_cycles WHERE id = ?", [$id]);
}

redirect($redirect);
