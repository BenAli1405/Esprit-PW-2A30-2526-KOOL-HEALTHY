<?php
require_once __DIR__ . '/config.php';
$db = config::getConnexion();
$queries = [
    "CREATE TABLE IF NOT EXISTS `notifications` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `utilisateur_id` INT NOT NULL,
      `message` TEXT NOT NULL,
      `lu` TINYINT(1) DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "ALTER TABLE `utilisateurs` ADD COLUMN `points_bonus` INT DEFAULT 0"
];

foreach($queries as $q) {
    try {
        $db->exec($q);
        echo "Succes: $q <br>";
    } catch(Exception $e) {
        echo "Erreur ou déjà existant sur $q : " . $e->getMessage() . "<br>";
    }
}
?>
