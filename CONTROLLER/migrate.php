<?php
require_once __DIR__ . '/../config.php';

// Simple migration helper: adds `avatar` column to `utilisateurs` if missing.
try {
    $db = config::getConnexion();

    $stmt = $db->prepare("SHOW COLUMNS FROM `utilisateurs` LIKE 'avatar'");
    $stmt->execute();

    if ($stmt->fetchColumn() === false) {
        $db->exec("ALTER TABLE `utilisateurs` ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL AFTER `email`");
        echo "OK: colonne 'avatar' ajoutee avec succes.";
    } else {
        echo "Info: colonne 'avatar' existe deja.";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Erreur lors de la migration: " . $e->getMessage();
}
