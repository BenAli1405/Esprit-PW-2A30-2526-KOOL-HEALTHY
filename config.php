<?php
// ========== KOOL HEALTHY - CONFIGURATION ==========
// Environment-aware shared configuration for the project.

// Database constants (can be overridden via environment variables)
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'kool_healthy');
}

// Application constants
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Kool Healthy');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/integweb/');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/');
}

if (!defined('MODEL_PATH')) {
    define('MODEL_PATH', ROOT_PATH . 'MODEL/');
}

if (!defined('CONTROLLER_PATH')) {
    define('CONTROLLER_PATH', ROOT_PATH . 'CONTROLLER/');
}

if (!defined('VIEW_PATH')) {
    define('VIEW_PATH', ROOT_PATH . 'VIEW/');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========== Compatibility class used by some controllers ==========
class config
{
    private static $pdo = null;
    private static $host = DB_HOST;
    private static $port = DB_PORT;
    private static $user = DB_USER;
    private static $password = DB_PASS;
    private static $database = DB_NAME;

    // Google / mail settings (can be set via env vars)
    private static $googleClientId = '';
    private static $googleClientSecret = '';
    private static $googleRedirectUri = '';
    private static $mailFrom = '';

    private static function initSecrets()
    {
        // initialize secret fields lazily from environment or defaults
        if (self::$googleClientId === '') {
            self::$googleClientId = getenv('GOOGLE_CLIENT_ID') ?: '';
        }
        if (self::$googleClientSecret === '') {
            self::$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
        }
        if (self::$googleRedirectUri === '') {
            self::$googleRedirectUri = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8080/Recettes/CONTROLLER/AuthController.php?action=google_callback';
        }
        if (self::$mailFrom === '') {
            self::$mailFrom = getenv('MAIL_FROM') ?: 'omarzehift52@gmail.com';
        }
    }

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                // ensure database exists (best-effort)
                self::initialiserBaseDeDonnees();

                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$database . ';charset=utf8mb4',
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    private static function initialiserBaseDeDonnees()
    {
        try {
            $pdo = new PDO(
                'mysql:host=' . self::$host . ';port=' . self::$port,
                self::$user,
                self::$password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // create database if it does not exist
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . self::$database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . self::$database . '`');
        } catch (Exception $e) {
            // don't kill the process here; higher-level code may handle DB creation differently
            error_log('initialiserBaseDeDonnees error: ' . $e->getMessage());
        }
    }

    public static function getGoogleClientId()
    {
        self::initSecrets();
        return trim((string) self::$googleClientId);
    }

    public static function getGoogleClientSecret()
    {
        self::initSecrets();
        return trim((string) self::$googleClientSecret);
    }

    public static function getGoogleRedirectUri()
    {
        self::initSecrets();
        return trim((string) self::$googleRedirectUri);
    }

    public static function getMailFrom()
    {
        self::initSecrets();
        return trim((string) self::$mailFrom);
    }
}

?>