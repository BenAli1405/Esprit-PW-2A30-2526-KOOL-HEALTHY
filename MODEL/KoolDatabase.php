<?php
/**
 * Database.php - MySQL Database Connection (Singleton)
 *
 * Configured for XAMPP / phpMyAdmin defaults.
 * Database: kool_healthy
 * Tables: produits, vente
 */
class KoolDatabase {

    private const DB_HOST = 'localhost';
    private const DB_NAME = 'kool_healthy';
    private const DB_USER = 'root';
    private const DB_PASSWORD = '';
    private const DB_PORT = 3306;

    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::DB_HOST . ';port=' . self::DB_PORT . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die('<p style="color:red;">DB Connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>');
            }
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
