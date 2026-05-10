<?php
require_once __DIR__ . '/../config.php';

// Migration helper: keep legacy avatar migration and add schema for plan/repas module.
try {
    $db = config::getConnexion();

    $stmt = $db->prepare("SHOW COLUMNS FROM `utilisateurs` LIKE 'avatar'");
    $stmt->execute();

    if ($stmt->fetchColumn() === false) {
        $db->exec("ALTER TABLE `utilisateurs` ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL AFTER `email`");
        echo "OK: colonne 'avatar' ajoutee avec succes.\n";
    } else {
        echo "Info: colonne 'avatar' existe deja.\n";
    }

    $db->exec(
        "CREATE TABLE IF NOT EXISTS `plan` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nom` VARCHAR(200) NOT NULL,
            `objectif` ENUM('perte-poids', 'maintien', 'prise-muscle') NOT NULL,
            `utilisateur_id` INT NOT NULL,
            `duree` INT NOT NULL,
            `preference` VARCHAR(120) NOT NULL,
            `allergies` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_plan_utilisateur` (`utilisateur_id`),
            CONSTRAINT `fk_plan_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    echo "OK: table 'plan' verifiee.\n";

    $db->exec(
        "CREATE TABLE IF NOT EXISTS `repas` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `plan_id` INT NOT NULL,
            `nom_recette` VARCHAR(255) NOT NULL,
            `date` DATE NOT NULL,
            `type_repas` ENUM('petit_dejeuner', 'dejeuner', 'diner', 'collation') NOT NULL,
            `statut` ENUM('prevu', 'consomme', 'annule') NOT NULL DEFAULT 'prevu',
            `calories_consommees` INT NULL,
            `heure_prevue` TIME NULL,
            `heure_reelle` TIME NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_repas_plan_date` (`plan_id`, `date`),
            CONSTRAINT `fk_repas_plan` FOREIGN KEY (`plan_id`) REFERENCES `plan` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    echo "OK: table 'repas' verifiee.\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "Erreur lors de la migration: " . $e->getMessage();
}
