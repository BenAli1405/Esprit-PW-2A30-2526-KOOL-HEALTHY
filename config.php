<?php

class config
{
    private static $pdo = null;
    private static $host = '127.0.0.1';
    private static $port = '3307';
    private static $user = 'root';
    private static $password = '';
    private static $database = 'projetweb';

    // Optional local fallback values; keep secrets out of the repository.
    private static $googleClientId = '';
    private static $googleClientSecret = '';
    private static $googleRedirectUri = 'http://localhost:8080/Recettes/CONTROLLER/AuthController.php?action=google_callback';
    private static $mailFrom = 'no-reply@koolhealthy.local';

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$database . ';charset=utf8mb4',
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    public static function getGoogleClientId()
    {
        return trim((string) (getenv('GOOGLE_CLIENT_ID') ?: self::$googleClientId));
    }

    public static function getGoogleClientSecret()
    {
        return trim((string) (getenv('GOOGLE_CLIENT_SECRET') ?: self::$googleClientSecret));
    }

    public static function getGoogleRedirectUri()
    {
        return trim((string) (getenv('GOOGLE_REDIRECT_URI') ?: self::$googleRedirectUri));
    }

    public static function getMailFrom()
    {
        return trim((string) (getenv('MAIL_FROM') ?: self::$mailFrom));
    }
}
?>
