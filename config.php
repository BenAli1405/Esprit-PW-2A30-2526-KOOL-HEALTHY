<?php
// ========== KOOL HEALTHY - CONFIGURATION ==========
// Environment-aware shared configuration for the project.

// Database constants (can be overridden via environment variables)
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3307');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'projetweb');
}

// Application constants
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Kool Healthy');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost:8080/integweb/');
}

// SMTP / Mail settings (environment-aware)
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.mailtrap.io');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', getenv('SMTP_PORT') ?: '587');
}

if (!defined('SMTP_USER')) {
    define('SMTP_USER', getenv('SMTP_USER') ?: '');
}

if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
}

if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Kool Healthy');
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
    private static $host = '127.0.0.1';
    private static $port = '3307';
    private static $user = 'root';
    private static $password = '';
    private static $database = 'projetweb';

    // Google / mail settings (can be set via env vars)
    private static $googleClientId = '';
    private static $googleClientSecret = '';
    private static $googleRedirectUri = 'http://localhost:8080/integweb/CONTROLLER/AuthController.php?action=google_callback';
    private static $mailFrom = 'omarzehift52@gmail.com';
    // SMTP fields (will be initialized from env if empty)
    private static $smtpHost = '';
    private static $smtpPort = '';
    private static $smtpUser = '';
    private static $smtpPass = '';
    private static $smtpSecure = '';
    private static $mailFromName = '';

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
            $defaultGoogleRedirect = (defined('BASE_URL') ? BASE_URL . 'CONTROLLER/AuthController.php?action=google_callback' : 'http://localhost/integweb/CONTROLLER/AuthController.php?action=google_callback');
            self::$googleRedirectUri = getenv('GOOGLE_REDIRECT_URI') ?: $defaultGoogleRedirect;
        }
        if (self::$mailFrom === '') {
            self::$mailFrom = getenv('MAIL_FROM') ?: 'omarzehift52@gmail.com';
        }
        // Initialize SMTP settings from environment variables if not set
        if (self::$smtpHost === '') {
            self::$smtpHost = getenv('SMTP_HOST') ?: SMTP_HOST;
        }
        if (self::$smtpPort === '') {
            self::$smtpPort = getenv('SMTP_PORT') ?: SMTP_PORT;
        }
        if (self::$smtpUser === '') {
            self::$smtpUser = getenv('SMTP_USER') ?: SMTP_USER;
        }
        if (self::$smtpPass === '') {
            self::$smtpPass = getenv('SMTP_PASS') ?: SMTP_PASS;
        }
        if (self::$smtpSecure === '') {
            self::$smtpSecure = getenv('SMTP_SECURE') ?: SMTP_SECURE;
        }
        if (self::$mailFromName === '') {
            self::$mailFromName = getenv('MAIL_FROM_NAME') ?: MAIL_FROM_NAME;
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

    /**
     * Retourne la configuration SMTP (hôte, port, user, pass, secure)
     * Utilisez ces valeurs pour initialiser PHPMailer ou une autre librairie.
     * @return array
     */
    public static function getSmtpConfig()
    {
        self::initSecrets();
        return [
            'host' => (string) self::$smtpHost,
            'port' => (int) self::$smtpPort,
            'username' => (string) self::$smtpUser,
            'password' => (string) self::$smtpPass,
            'secure' => (string) self::$smtpSecure,
            'from' => (string) self::$mailFrom,
            'from_name' => (string) self::$mailFromName,
        ];
    }
}

?>