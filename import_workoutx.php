<?php
/**
 * Script d'importation globale des exercices depuis l'API WorkoutX vers la table `exercice_reference`.
 * Utilise la pagination pour récupérer un maximum d'exercices avec une NORMALISATION AMÉLIORÉE.
 */

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/api_keys.php';

// Augmenter la limite de temps pour le script
set_time_limit(300);

if (!defined('WORKOUTX_API_KEY') || empty(trim(WORKOUTX_API_KEY))) {
    die("Erreur : La constante WORKOUTX_API_KEY n'est pas définie ou est vide.\n");
}

$apiKey = trim(WORKOUTX_API_KEY);
$baseUrl = defined('WORKOUTX_API_BASE_URL') ? WORKOUTX_API_BASE_URL : 'https://api.workoutxapp.com/v1';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "Connexion à la base de données réussie.<br>\n";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage() . "\n");
}

// Requête d'insertion sécurisée avec mise à jour en cas de doublon sur le nom
$upsertStmt = $pdo->prepare("
    INSERT INTO exercice_reference
        (nom, equipement, difficulte, cible_musculaire, intensite_calorique, type_mouvement, groupe_primaire)
    VALUES
        (:nom, :equipement, :difficulte, :cible, :intensite, :type_mvt, :groupe)
    ON DUPLICATE KEY UPDATE 
    equipement          = VALUES(equipement),
    difficulte          = VALUES(difficulte),
    cible_musculaire    = VALUES(cible_musculaire),
    intensite_calorique = VALUES(intensite_calorique),
    type_mouvement      = VALUES(type_mouvement),
    groupe_primaire     = VALUES(groupe_primaire)
");

echo "Début de l'importation massive depuis l'API WorkoutX (Normalisation améliorée)...<br><br>\n";

$limit = 20;
$offset = 0;
$totalProcessed = 0;
$maxPages = 15; // Sécurité pour éviter boucle infinie
$page = 1;

while ($page <= $maxPages) {
    echo "Récupération de la page $page (offset: $offset)...<br>\n";
    
    $url = $baseUrl . "/exercises?limit=$limit&offset=$offset";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'X-WorkoutX-Key: ' . $apiKey,
                'Accept: application/json'
            ],
            'timeout' => 15
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        echo "&nbsp;&nbsp;-> <span style='color:red;'>[Erreur API]</span> Impossible de joindre l'API : " . ($error['message'] ?? 'Erreur inconnue') . "<br>\n";
        if (strpos($error['message'] ?? '', '401') !== false) {
            echo "&nbsp;&nbsp;-> <span style='color:red;'>[Arrêt]</span> Clé API refusée (401 Unauthorized).<br>\n";
        }
        break; 
    }
    
    $data = json_decode($response, true);
    
    $exercisesData = [];
    if (isset($data['data']) && is_array($data['data'])) {
        $exercisesData = $data['data'];
    } elseif (is_array($data) && !isset($data['error'])) {
        $exercisesData = $data;
    }
    
    if (empty($exercisesData)) {
        echo "&nbsp;&nbsp;-> Fin des résultats.<br>\n";
        break;
    }
    
    $countPage = 0;
    foreach ($exercisesData as $apiExercise) {
        $rawName = $apiExercise['name'] ?? null;
        if (!$rawName) continue;
        
        $rawEquipment = strtolower($apiExercise['equipment'] ?? 'none');
        $rawDifficulty = strtolower($apiExercise['difficulty'] ?? 'intermediate');
        // Priorité à target, sinon bodyPart
        $rawBodyPart = strtolower($apiExercise['target'] ?? $apiExercise['bodyPart'] ?? 'none');
        $rawCalories = isset($apiExercise['caloriesPerMinute']) ? (float)$apiExercise['caloriesPerMinute'] : null;
        
        // 1. Normalisation ÉQUIPEMENT
        if (strpos($rawEquipment, 'body weight') !== false) $normalizedEquipment = 0.1;
        elseif (strpos($rawEquipment, 'dumbbell') !== false) $normalizedEquipment = 0.5;
        elseif (strpos($rawEquipment, 'barbell') !== false) $normalizedEquipment = 0.8;
        elseif (strpos($rawEquipment, 'cable') !== false) $normalizedEquipment = 0.6;
        elseif (strpos($rawEquipment, 'machine') !== false) $normalizedEquipment = 1.0;
        elseif (strpos($rawEquipment, 'band') !== false || strpos($rawEquipment, 'kettlebell') !== false) $normalizedEquipment = 0.4;
        elseif ($rawEquipment === 'none') $normalizedEquipment = 0.0;
        else $normalizedEquipment = 0.4; // Défaut
        
        // 2. Normalisation DIFFICULTÉ
        if (strpos($rawDifficulty, 'beginner') !== false) $normalizedDifficulty = 0.2;
        elseif (strpos($rawDifficulty, 'intermediate') !== false) $normalizedDifficulty = 0.6;
        elseif (strpos($rawDifficulty, 'advanced') !== false) $normalizedDifficulty = 1.0;
        else $normalizedDifficulty = 0.5; // Défaut
        
        // 3. Normalisation CIBLE MUSCULAIRE (groupe large)
        if (preg_match('/leg|quad|glute|thigh|hamstring|calf/', $rawBodyPart)) {
            $normalizedBodyPart = 0.2;
        } elseif (preg_match('/chest|pectoral|pec/', $rawBodyPart)) {
            $normalizedBodyPart = 0.8;
        } elseif (preg_match('/back|lat|lats|bicep|tricep/', $rawBodyPart)) {
            $normalizedBodyPart = 0.7;
        } elseif (preg_match('/shoulder|deltoid|military|press/', $rawBodyPart)) {
            $normalizedBodyPart = 0.9;
        } elseif (preg_match('/ab|core|waist/', $rawBodyPart)) {
            $normalizedBodyPart = 0.5;
        } else {
            $normalizedBodyPart = 0.5; // Défaut
        }
        
        // 4. Normalisation INTENSITÉ CALORIQUE
        if ($rawCalories !== null) {
            $normalizedIntensite = min(1.0, $rawCalories / 15.0);
        } else {
            // Estimation
            $normalizedIntensite = 0.3 + ($normalizedDifficulty * 0.5);
            $normalizedIntensite = min(1.0, max(0.0, $normalizedIntensite));
        }

        // 5. Normalisation TYPE DE MOUVEMENT
        // 0.1=mobilité  0.3=isolation  0.5=cardio  0.7=plyométrie  0.8=compound  1.0=olympique
        $rawType = strtolower($apiExercise['type'] ?? $apiExercise['category'] ?? 'none');
        if (preg_match('/powerlifting|olympic|snatch|clean|jerk/', $rawType . ' ' . $rawBodyPart)) {
            $normalizedTypeMvt = 1.0;
        } elseif (preg_match('/compound|multi.joint|squat|deadlift|press|row|pull/', $rawType . ' ' . $rawBodyPart)) {
            $normalizedTypeMvt = 0.8;
        } elseif (preg_match('/plyometric|jump|explosive/', $rawType)) {
            $normalizedTypeMvt = 0.7;
        } elseif (preg_match('/cardio|aerobic|hiit|running|cycling/', $rawType)) {
            $normalizedTypeMvt = 0.5;
        } elseif (preg_match('/isolation|curl|extension|fly|raise|kickback/', $rawType . ' ' . $rawBodyPart)) {
            $normalizedTypeMvt = 0.3;
        } elseif (preg_match('/stretch|mobility|yoga|flexibility/', $rawType)) {
            $normalizedTypeMvt = 0.1;
        } else {
            $normalizedTypeMvt = 0.5; // Défaut
        }

        // 6. Normalisation GROUPE PRIMAIRE (muscle principal, granularité fine)
        if (preg_match('/quadricep|quad/', $rawBodyPart)) {
            $normalizedGroupe = 0.15;
        } elseif (preg_match('/hamstring/', $rawBodyPart)) {
            $normalizedGroupe = 0.20;
        } elseif (preg_match('/glute|buttock/', $rawBodyPart)) {
            $normalizedGroupe = 0.25;
        } elseif (preg_match('/calf|gastrocnemius|soleus/', $rawBodyPart)) {
            $normalizedGroupe = 0.30;
        } elseif (preg_match('/ab|rectus abdominis|oblique/', $rawBodyPart)) {
            $normalizedGroupe = 0.40;
        } elseif (preg_match('/core|transverse|plank/', $rawBodyPart)) {
            $normalizedGroupe = 0.45;
        } elseif (preg_match('/forearm|grip|wrist/', $rawBodyPart)) {
            $normalizedGroupe = 0.50;
        } elseif (preg_match('/bicep/', $rawBodyPart)) {
            $normalizedGroupe = 0.55;
        } elseif (preg_match('/tricep/', $rawBodyPart)) {
            $normalizedGroupe = 0.60;
        } elseif (preg_match('/lat|latissimus/', $rawBodyPart)) {
            $normalizedGroupe = 0.65;
        } elseif (preg_match('/back|rhomboid|trapezius|trap/', $rawBodyPart)) {
            $normalizedGroupe = 0.75;
        } elseif (preg_match('/shoulder|deltoid|delt/', $rawBodyPart)) {
            $normalizedGroupe = 0.80;
        } elseif (preg_match('/chest|pectoral|pec/', $rawBodyPart)) {
            $normalizedGroupe = 0.85;
        } elseif (preg_match('/leg|thigh/', $rawBodyPart)) {
            $normalizedGroupe = 0.20;
        } else {
            $normalizedGroupe = 0.50; // Défaut
        }
        
        // Insertion / Mise à jour (6 features)
        try {
            $upsertStmt->execute([
                ':nom'         => trim($rawName),
                ':equipement'  => $normalizedEquipment,
                ':difficulte'  => $normalizedDifficulty,
                ':cible'       => $normalizedBodyPart,
                ':intensite'   => $normalizedIntensite,
                ':type_mvt'    => $normalizedTypeMvt,
                ':groupe'      => $normalizedGroupe,
            ]);
            $countPage++;
        } catch (PDOException $e) {
            echo "<!-- Erreur DB pour $rawName: " . htmlspecialchars($e->getMessage()) . " -->\n";
        }
    }
    
    echo "&nbsp;&nbsp;-> <span style='color:green;'>$countPage exercices traités (insérés ou mis à jour)</span>.<br>\n";
    $totalProcessed += $countPage;
    
    $offset += $limit;
    $page++;
    
    // Pause pour respecter les quotas de l'API
    usleep(500000); 
}

echo "<br><strong>Importation terminée !</strong> Total d'exercices traités : $totalProcessed\n";
