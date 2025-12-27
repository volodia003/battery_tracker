<?php
require_once 'config/database.php';
require_once 'config/auth.php';

logout();
redirect('login.php');
