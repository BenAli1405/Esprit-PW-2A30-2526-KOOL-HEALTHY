<?php
// ========== KOOL HEALTHY - CONFIGURATION ==========
// This file contains the database and application configuration

// Database Configuration (adjust if using a real database)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web');

// Application Configuration
define('APP_NAME', 'Kool Healthy');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/all/');

// Define paths
define('ROOT_PATH', dirname(__FILE__) . '/');
define('MODEL_PATH', ROOT_PATH . 'MODEL/');
define('CONTROLLER_PATH', ROOT_PATH . 'CONTROLLER/');
define('VIEW_PATH', ROOT_PATH . 'VIEW/');

// Start session
session_start();
?>
