<?php

class config
{
    private static $pdo      = null;
    private static $host     = '127.0.0.1';
    private static $port     = '3307';
    private static $user     = 'root';
    private static $password = '';
    private static $database = 'kool_healthy_db';

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::initialiserBaseDeDonnees();
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$database,
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
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
        $pdo = new PDO(
            'mysql:host=' . self::$host . ';port=' . self::$port,
            self::$user,
            self::$password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . self::$database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . self::$database . '`');

        // Table utilisateurs (partagée avec les autres modules)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `utilisateurs` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `nom`          VARCHAR(100) NOT NULL,
            `email`        VARCHAR(150) UNIQUE NOT NULL,
            `mot_de_passe` VARCHAR(255) NOT NULL,
            `role`         ENUM('utilisateur','admin') NOT NULL DEFAULT 'utilisateur',
            `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Table plans_nutritionnels
        $pdo->exec("CREATE TABLE IF NOT EXISTS `plans_nutritionnels` (
            `planID`                 INT AUTO_INCREMENT PRIMARY KEY,
            `nom`                    VARCHAR(255) NOT NULL,
            `calories_journalieres`  FLOAT NOT NULL DEFAULT 2000,
            `utilisateur_id`         INT NOT NULL,
            `date_debut`             DATE NOT NULL,
            `date_fin`               DATE NOT NULL,
            `statistiques`           FLOAT DEFAULT 0,
            `created_at`             DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_utilisateur` (`utilisateur_id`),
            CONSTRAINT `fk_plan_utilisateur`
                FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Table repas
        $pdo->exec("CREATE TABLE IF NOT EXISTS `repas` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `planID`     INT NOT NULL,
            `recette`    VARCHAR(255) NOT NULL,
            `date`       DATE NOT NULL,
            `type_repas` ENUM('petit-déjeuner','déjeuner','dîner','collation') NOT NULL DEFAULT 'déjeuner',
            `statut`     ENUM('planifié','consommé','annulé') NOT NULL DEFAULT 'planifié',
            INDEX `idx_plan` (`planID`),
            CONSTRAINT `fk_repas_plan`
                FOREIGN KEY (`planID`) REFERENCES `plans_nutritionnels`(`planID`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
?>
