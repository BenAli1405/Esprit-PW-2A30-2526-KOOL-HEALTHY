<?php

include_once __DIR__ . '/../config.php';

class Database
{
    public static function getConnection()
    {
        $config = config::getDbConfig();

        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']);
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $exception) {
            die('Erreur de connexion à la base de données : ' . $exception->getMessage());
        }
    }
}
