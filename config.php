<?php

class config
{
    private static $pdo = null;
    private static $host = '127.0.0.1';
    private static $port = '3306';
    private static $user = 'root';
    private static $password = '';
    private static $database = 'kool_healthy';

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::initialiserBaseDeDonnees();
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$database . ';charset=utf8mb4',
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
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
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $pdo->exec(
            'CREATE DATABASE IF NOT EXISTS `' . self::$database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );

        $pdo->exec('USE `' . self::$database . '`');

        // Table défis
        $pdo->exec("CREATE TABLE IF NOT EXISTS `defis` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `titre` VARCHAR(255) NOT NULL,
            `type` VARCHAR(50) NOT NULL DEFAULT 'nutrition',
            `points` INT NOT NULL DEFAULT 0,
            `date_debut` DATE,
            `date_fin` DATE,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Table utilisateurs
        $pdo->exec("CREATE TABLE IF NOT EXISTS `utilisateurs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nom` VARCHAR(150) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `mot_de_passe` VARCHAR(255) DEFAULT NULL,
            `role` VARCHAR(50) NOT NULL DEFAULT 'utilisateur',
            `poids` DECIMAL(5,2) DEFAULT NULL,
            `taille` DECIMAL(5,2) DEFAULT NULL,
            `imc` DECIMAL(5,2) DEFAULT NULL,
            `objectif` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Table profil_nutritif
        $pdo->exec("CREATE TABLE IF NOT EXISTS `profil_nutritif` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `utilisateur` INT NOT NULL,
            `age` INT DEFAULT NULL,
            `allergies` TEXT DEFAULT NULL,
            `besoins_caloriques` INT DEFAULT NULL,
            FOREIGN KEY (`utilisateur`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Table participations
        $pdo->exec("CREATE TABLE IF NOT EXISTS `participations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `utilisateur_id` INT NOT NULL,
            `defi_id` INT NOT NULL,
            `progression` INT NOT NULL DEFAULT 0,
            `termine` TINYINT(1) NOT NULL DEFAULT 0,
            `points_gagnes` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_participation` (`utilisateur_id`, `defi_id`),
            FOREIGN KEY (`defi_id`) REFERENCES `defis`(`id`) ON DELETE CASCADE,
            INDEX `idx_utilisateur` (`utilisateur_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Données exemple - défis
        $verifDefi = $pdo->query("SELECT COUNT(*) FROM defis");
        if ((int) $verifDefi->fetchColumn() === 0) {
            $pdo->exec("INSERT INTO `defis` (`titre`, `type`, `points`, `date_debut`, `date_fin`) VALUES
                ('Manger 5 fruits/légumes par jour', 'nutrition', 50, '2025-03-01', '2025-03-31'),
                ('Réduire son empreinte carbone', 'ecologie', 100, '2025-03-01', '2025-04-15'),
                ('Tester 3 recettes végétales', 'recette', 75, '2025-03-10', '2025-04-10'),
                ('Partager un repas durable', 'social', 30, '2025-03-15', '2025-03-30')
            ");
        }

        // Données exemple - utilisateur par défaut
        $verifUtilisateur = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
        if ((int) $verifUtilisateur->fetchColumn() === 0) {
            $pdo->exec("INSERT INTO `utilisateurs` (`nom`, `email`, `role`, `mot_de_passe`, `created_at`) VALUES
                ('Visiteur', 'visiteur@local', 'utilisateur', '', NOW())");
        }
    }
}
?>
