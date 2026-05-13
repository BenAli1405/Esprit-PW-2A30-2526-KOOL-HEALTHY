<?php
/**
 * Script d'importation MANUELLE (secours) pour la table `exercice_reference`.
 * MIS À JOUR avec la nouvelle échelle de normalisation (Legs=0.2, Chest=0.8, Back=0.7, etc.)
 */

require_once __DIR__ . '/config/Database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 50 exercices cohérents
// Format: [equipement, difficulte, cible_musculaire, intensite]
// Cible: Legs=0.2, Core=0.5, Back/Arms=0.7, Chest=0.8, Shoulders=0.9
$mockData = [
    'Squat' => [0.8, 0.6, 0.2, 0.6], // Legs = 0.2
    'Pompes' => [0.1, 0.5, 0.8, 0.55], // Chest = 0.8
    'Traction' => [0.1, 0.8, 0.7, 0.7], // Back = 0.7
    'Crunch' => [0.1, 0.2, 0.5, 0.4], // Core = 0.5
    'Développé couché' => [0.8, 0.6, 0.8, 0.6], // Chest = 0.8
    'Burpees' => [0.1, 0.8, 0.2, 1.0], // Legs/Full = 0.2
    'Fentes' => [0.5, 0.5, 0.2, 0.55], // Legs = 0.2
    'Gainage' => [0.1, 0.5, 0.5, 0.4], // Core = 0.5
    'Soulevé de terre' => [0.8, 0.8, 0.7, 0.7], // Back = 0.7
    'Curl biceps' => [0.5, 0.3, 0.7, 0.4], // Arms = 0.7
    'Extension triceps' => [0.6, 0.4, 0.7, 0.4], // Arms = 0.7
    'Presse à cuisses' => [1.0, 0.5, 0.2, 0.55], // Legs = 0.2
    'Leg curl' => [1.0, 0.4, 0.2, 0.5], // Legs = 0.2
    'Tirage poitrine' => [1.0, 0.5, 0.7, 0.55], // Back = 0.7
    'Tirage horizontal' => [1.0, 0.5, 0.7, 0.55], // Back = 0.7
    'Rowing haltère' => [0.5, 0.6, 0.7, 0.6], // Back = 0.7
    'Dips' => [0.1, 0.7, 0.8, 0.65], // Chest = 0.8
    'Elévations latérales' => [0.5, 0.4, 0.9, 0.5], // Shoulders = 0.9
    'Développé militaire' => [0.8, 0.6, 0.9, 0.6], // Shoulders = 0.9
    'Mountain climbers' => [0.1, 0.6, 0.5, 0.6], // Core = 0.5
    'Jumping jacks' => [0.1, 0.2, 0.2, 0.6], // Legs/Cardio = 0.2
    'Corde à sauter' => [0.1, 0.4, 0.2, 0.7], // Legs = 0.2
    'Box jump' => [0.1, 0.7, 0.2, 0.8], // Legs = 0.2
    'Kettlebell swing' => [0.6, 0.6, 0.2, 0.6], // Legs = 0.2
    'Goblet squat' => [0.6, 0.4, 0.2, 0.5], // Legs = 0.2
    'Hip thrust' => [0.8, 0.5, 0.2, 0.55], // Legs = 0.2
    'Mollets debout' => [0.1, 0.2, 0.2, 0.4], // Legs = 0.2
    'Machine mollets' => [1.0, 0.3, 0.2, 0.45], // Legs = 0.2
    'Poulie vis-à-vis' => [0.6, 0.5, 0.8, 0.55], // Chest = 0.8
    'Peck deck' => [1.0, 0.3, 0.8, 0.45], // Chest = 0.8
    'Pull over' => [0.5, 0.6, 0.8, 0.6], // Chest = 0.8
    'Soulevé de terre roumain' => [0.8, 0.6, 0.2, 0.6], // Legs = 0.2
    'Leg extension' => [1.0, 0.4, 0.2, 0.5], // Legs = 0.2
    'Abductor machine' => [1.0, 0.3, 0.2, 0.45], // Legs = 0.2
    'Adductor machine' => [1.0, 0.3, 0.2, 0.45], // Legs = 0.2
    'Russian twist' => [0.1, 0.5, 0.5, 0.55], // Core = 0.5
    'Planche latérale' => [0.1, 0.6, 0.5, 0.6], // Core = 0.5
    'Dragon flag' => [0.1, 0.9, 0.5, 0.75], // Core = 0.5
    'Ab rollout' => [0.3, 0.8, 0.5, 0.7], // Core = 0.5
    'Soulevé de terre sumo' => [0.8, 0.7, 0.2, 0.65], // Legs = 0.2
    'Front squat' => [0.8, 0.7, 0.2, 0.65], // Legs = 0.2
    'Hack squat' => [1.0, 0.6, 0.2, 0.6], // Legs = 0.2
    'Zercher squat' => [0.8, 0.8, 0.2, 0.7], // Legs = 0.2
    'Bulgarian split squat' => [0.5, 0.7, 0.2, 0.65], // Legs = 0.2
    'Pistol squat' => [0.1, 0.9, 0.2, 0.75], // Legs = 0.2
    'Sissy squat' => [0.1, 0.8, 0.2, 0.7], // Legs = 0.2
    'Glute ham raise' => [0.3, 0.8, 0.2, 0.7], // Legs = 0.2
    'Good morning' => [0.8, 0.6, 0.2, 0.6], // Legs = 0.2
    'Hyperextension' => [0.3, 0.4, 0.7, 0.5], // Back = 0.7
    'Reverse hyperextension' => [0.3, 0.5, 0.7, 0.55], // Back = 0.7
];

echo "Début de l'importation manuelle de secours dans `exercice_reference`...<br><br>\n";

$upsertStmt = $pdo->prepare("
    INSERT INTO exercice_reference (nom, equipement, difficulte, cible_musculaire, intensite_calorique)
    VALUES (:nom, :equipement, :difficulte, :cible, :intensite)
    ON DUPLICATE KEY UPDATE 
    equipement = VALUES(equipement), 
    difficulte = VALUES(difficulte), 
    cible_musculaire = VALUES(cible_musculaire), 
    intensite_calorique = VALUES(intensite_calorique)
");

$count = 0;
foreach ($mockData as $nom => $data) {
    try {
        $upsertStmt->execute([
            ':nom'         => $nom,
            ':equipement'  => $data[0],
            ':difficulte'  => $data[1],
            ':cible'       => $data[2],
            ':intensite'   => $data[3]
        ]);
        $count++;
    } catch (PDOException $e) {
        echo "-> <span style='color:red;'>[Erreur DB]</span> pour $nom : " . $e->getMessage() . "<br>\n";
    }
}

echo "<br><strong>Importation manuelle terminée !</strong> $count exercices importés/mis à jour.";
