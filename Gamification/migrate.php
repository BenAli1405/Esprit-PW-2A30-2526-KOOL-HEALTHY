<?php
require_once __DIR__ . '/config.php';
$db = config::getConnexion();
$queries = [
    "ALTER TABLE defis ADD COLUMN status VARCHAR(20) DEFAULT 'approuve'",
    "ALTER TABLE defis ADD COLUMN proposant_id INT DEFAULT NULL",
    "ALTER TABLE defis ADD COLUMN restrictions TEXT DEFAULT NULL"
];
foreach($queries as $q) {
    try {
        $db->exec($q);
        echo "Succes: $q <br>";
    } catch(Exception $e) {
        echo "Erreur sur $q : " . $e->getMessage() . "<br>";
    }
}
?>
