<?php
require_once 'config/database.php';
require_once 'config/auth.php';

header('Content-Type: application/json');

$theme = toggleTheme();

echo json_encode(['theme' => $theme]);
