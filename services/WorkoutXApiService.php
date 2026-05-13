<?php

require_once __DIR__ . '/../config/api_keys.php';

/**
 * Service pour interagir avec l'API WorkoutX
 * Récupère les données brutes des exercices, normalise les features
 * et les persiste dans exercice_reference via PDO.
 *
 * Règles de normalisation (cohérentes avec import_workoutx.php) :
 * - equipement    : body weight→0.1, band/kettlebell→0.4, dumbbell→0.5,
 *                   cable→0.6, barbell→0.8, machine→1.0, none→0.0, défaut→0.4
 * - difficulte    : beginner→0.2, intermediate→0.6, advanced→1.0, défaut→0.5
 * - cible         : legs/glutes→0.2, abs/core→0.5, back/lat/bicep/tricep→0.7,
 *                   chest→0.8, shoulder→0.9, défaut→0.5
 * - intensite     : si caloriesPerMinute → min(1, cal/15)
 *                   sinon               → min(1, 0.3 + difficulte×0.5)
 */
class WorkoutXApiService
{
    private $apiKey;
    private $baseUrl;
    private $timeout = 10;
    private $pdo; // PDO optionnel – requis uniquement pour fetchAndInsertExercise()

    /**
     * @param string|null $apiKey Clé API WorkoutX (utilise la constante par défaut si null)
     * @param \PDO|null   $pdo   Connexion PDO (nécessaire pour l'insertion en base)
     */
    public function __construct(?string $apiKey = null, ?\PDO $pdo = null)
    {
        $this->apiKey  = $apiKey ?? WORKOUTX_API_KEY;
        $this->baseUrl = WORKOUTX_API_BASE_URL;
        $this->pdo     = $pdo;

        if ($this->apiKey === 'YOUR_API_KEY_HERE') {
            throw new Exception('Clé API WorkoutX non configurée. Veuillez renseigner WORKOUTX_API_KEY dans config/api_keys.php');
        }
    }

    // =========================================================================
    // MÉTHODE PRINCIPALE : fetch → normalize → insert
    // =========================================================================

    /**
     * Récupère un exercice depuis l'API WorkoutX par son nom,
     * le normalise selon les règles de import_workoutx.php,
     * l'insère (ou le met à jour) dans exercice_reference,
     * et retourne son ID en base.
     *
     * @param  string $name  Nom de l'exercice saisi par l'utilisateur
     * @return int|null      ID inséré/existant, ou null si introuvable / données insuffisantes
     * @throws \RuntimeException Si le PDO n'est pas injecté
     */
    public function fetchAndInsertExercise(string $name): ?int
    {
        if ($this->pdo === null) {
            throw new \RuntimeException(
                'Un objet PDO doit être passé au constructeur pour utiliser fetchAndInsertExercise().'
            );
        }

        $name = trim($name);
        if ($name === '') {
            return null;
        }

        // 1. Interroger l'API WorkoutX
        $url      = $this->baseUrl . '/exercises?name=' . urlencode($name);
        $response = @file_get_contents($url, false, $this->buildContext());

        if ($response === false) {
            return null; // API injoignable
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
            return null;
        }

        // 2. Extraire le premier résultat pertinent (gestion multi-format API)
        $exercises = [];
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            $exercises = $decoded['data'];
        } elseif (isset($decoded['exercises']) && is_array($decoded['exercises'])) {
            $exercises = $decoded['exercises'];
        } elseif (is_array($decoded) && isset($decoded[0])) {
            $exercises = $decoded;
        } elseif (is_array($decoded) && isset($decoded['name'])) {
            $exercises = [$decoded]; // Réponse directe d'un seul exercice
        }

        if (empty($exercises)) {
            return null;
        }

        $apiExercise = $exercises[0];

        // 3. Récupérer le nom (fallback sur la saisie utilisateur)
        $rawName = trim($apiExercise['name'] ?? '');
        if ($rawName === '') {
            $rawName = $name;
        }

        // 4. Normaliser – même logique que import_workoutx.php (strpos-based)
        $features = $this->normalizeFromRaw($apiExercise);

        // 5. Insérer ou mettre à jour dans exercice_reference
        return $this->insertNormalized($rawName, $features);
    }

    // =========================================================================
    // NORMALISATION (compatible import_workoutx.php)
    // =========================================================================

    /**
     * Normalise un exercice brut de l'API (6 features).
     * Règles identiques à import_workoutx.php (strpos/preg_match).
     *
     * @param  array $apiExercise  Exercice brut retourné par l'API WorkoutX
     * @return array               6 clés : equipement, difficulte, cible_musculaire,
     *                             intensite_calorique, type_mouvement, groupe_primaire
     */
    public function normalizeFromRaw(array $apiExercise): array
    {
        $rawEquipment  = strtolower($apiExercise['equipment']  ?? 'none');
        $rawDifficulty = strtolower($apiExercise['difficulty'] ?? 'intermediate');
        $rawBodyPart   = strtolower($apiExercise['target'] ?? $apiExercise['bodyPart'] ?? 'none');
        $rawType       = strtolower($apiExercise['type']   ?? $apiExercise['category'] ?? 'none');
        $rawCalories   = isset($apiExercise['caloriesPerMinute'])
                         ? (float)$apiExercise['caloriesPerMinute']
                         : null;

        // --- Équipement ---
        if (strpos($rawEquipment, 'body weight') !== false) {
            $normEquipment = 0.1;
        } elseif (strpos($rawEquipment, 'dumbbell') !== false) {
            $normEquipment = 0.5;
        } elseif (strpos($rawEquipment, 'barbell') !== false) {
            $normEquipment = 0.8;
        } elseif (strpos($rawEquipment, 'cable') !== false) {
            $normEquipment = 0.6;
        } elseif (strpos($rawEquipment, 'machine') !== false) {
            $normEquipment = 1.0;
        } elseif (strpos($rawEquipment, 'band') !== false || strpos($rawEquipment, 'kettlebell') !== false) {
            $normEquipment = 0.4;
        } elseif ($rawEquipment === 'none') {
            $normEquipment = 0.0;
        } else {
            $normEquipment = 0.4;
        }

        // --- Difficulté ---
        if (strpos($rawDifficulty, 'beginner') !== false) {
            $normDifficulty = 0.2;
        } elseif (strpos($rawDifficulty, 'intermediate') !== false) {
            $normDifficulty = 0.6;
        } elseif (strpos($rawDifficulty, 'advanced') !== false) {
            $normDifficulty = 1.0;
        } else {
            $normDifficulty = 0.5;
        }

        // --- Cible musculaire (groupe large) ---
        if (preg_match('/leg|quad|glute|thigh|hamstring|calf/', $rawBodyPart)) {
            $normBodyPart = 0.2;
        } elseif (preg_match('/chest|pectoral|pec/', $rawBodyPart)) {
            $normBodyPart = 0.8;
        } elseif (preg_match('/back|lat|lats|bicep|tricep/', $rawBodyPart)) {
            $normBodyPart = 0.7;
        } elseif (preg_match('/shoulder|deltoid|military|press/', $rawBodyPart)) {
            $normBodyPart = 0.9;
        } elseif (preg_match('/ab|core|waist/', $rawBodyPart)) {
            $normBodyPart = 0.5;
        } else {
            $normBodyPart = 0.5;
        }

        // --- Intensité calorique ---
        if ($rawCalories !== null) {
            $normIntensite = min(1.0, $rawCalories / 15.0);
        } else {
            $normIntensite = min(1.0, max(0.0, 0.3 + ($normDifficulty * 0.5)));
        }

        // --- Type de mouvement ---
        // 0.1=mobilité/étirement  0.3=isolation  0.5=cardio/fonctionnel
        // 0.7=plyométrie          0.8=compound    1.0=powerlifting/olympique
        if (preg_match('/powerlifting|olympic|snatch|clean|jerk/', $rawType . ' ' . $rawBodyPart)) {
            $normTypeMvt = 1.0;
        } elseif (preg_match('/compound|multi.joint|squat|deadlift|press|row|pull/', $rawType . ' ' . $rawBodyPart)) {
            $normTypeMvt = 0.8;
        } elseif (preg_match('/plyometric|jump|explosive/', $rawType)) {
            $normTypeMvt = 0.7;
        } elseif (preg_match('/cardio|aerobic|hiit|running|cycling/', $rawType)) {
            $normTypeMvt = 0.5;
        } elseif (preg_match('/isolation|curl|extension|fly|raise|kickback/', $rawType . ' ' . $rawBodyPart)) {
            $normTypeMvt = 0.3;
        } elseif (preg_match('/stretch|mobility|yoga|flexibility/', $rawType)) {
            $normTypeMvt = 0.1;
        } else {
            $normTypeMvt = 0.5; // Défaut
        }

        // --- Groupe primaire (muscle principal, granularité fine) ---
        // Distinct de cible_musculaire : plus précis sur le muscle exact
        if (preg_match('/quadricep|quad/', $rawBodyPart)) {
            $normGroupe = 0.15;
        } elseif (preg_match('/hamstring/', $rawBodyPart)) {
            $normGroupe = 0.20;
        } elseif (preg_match('/glute|buttock/', $rawBodyPart)) {
            $normGroupe = 0.25;
        } elseif (preg_match('/calf|gastrocnemius|soleus/', $rawBodyPart)) {
            $normGroupe = 0.30;
        } elseif (preg_match('/ab|rectus abdominis|oblique/', $rawBodyPart)) {
            $normGroupe = 0.40;
        } elseif (preg_match('/core|transverse|plank/', $rawBodyPart)) {
            $normGroupe = 0.45;
        } elseif (preg_match('/forearm|grip|wrist/', $rawBodyPart)) {
            $normGroupe = 0.50;
        } elseif (preg_match('/bicep/', $rawBodyPart)) {
            $normGroupe = 0.55;
        } elseif (preg_match('/tricep/', $rawBodyPart)) {
            $normGroupe = 0.60;
        } elseif (preg_match('/lat|latissimus/', $rawBodyPart)) {
            $normGroupe = 0.65;
        } elseif (preg_match('/back|rhomboid|trapezius|trap/', $rawBodyPart)) {
            $normGroupe = 0.75;
        } elseif (preg_match('/shoulder|deltoid|delt/', $rawBodyPart)) {
            $normGroupe = 0.80;
        } elseif (preg_match('/chest|pectoral|pec/', $rawBodyPart)) {
            $normGroupe = 0.85;
        } elseif (preg_match('/leg|thigh/', $rawBodyPart)) {
            $normGroupe = 0.20;
        } else {
            $normGroupe = 0.50; // Défaut
        }

        return [
            'equipement'          => round($normEquipment,  2),
            'difficulte'          => round($normDifficulty, 2),
            'cible_musculaire'    => round($normBodyPart,   2),
            'intensite_calorique' => round($normIntensite,  2),
            'type_mouvement'      => round($normTypeMvt,    2),
            'groupe_primaire'     => round($normGroupe,     2),
        ];
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    /**
     * Insère ou met à jour un exercice normalisé dans exercice_reference (6 features).
     * Utilise ON DUPLICATE KEY UPDATE (la colonne `nom` doit avoir un UNIQUE KEY).
     *
     * @param  string $nom      Nom de l'exercice
     * @param  array  $features 6 features normalisées
     * @return int              ID de la ligne insérée ou existante
     */
    private function insertNormalized(string $nom, array $features): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO exercice_reference
                (nom, equipement, difficulte, cible_musculaire,
                 intensite_calorique, type_mouvement, groupe_primaire)
            VALUES
                (:nom, :equipement, :difficulte, :cible,
                 :intensite, :type_mvt, :groupe)
            ON DUPLICATE KEY UPDATE
                equipement          = VALUES(equipement),
                difficulte          = VALUES(difficulte),
                cible_musculaire    = VALUES(cible_musculaire),
                intensite_calorique = VALUES(intensite_calorique),
                type_mouvement      = VALUES(type_mouvement),
                groupe_primaire     = VALUES(groupe_primaire)
        ");

        $stmt->execute([
            ':nom'        => $nom,
            ':equipement' => $features['equipement'],
            ':difficulte' => $features['difficulte'],
            ':cible'      => $features['cible_musculaire'],
            ':intensite'  => $features['intensite_calorique'],
            ':type_mvt'   => $features['type_mouvement']  ?? 0.5,
            ':groupe'     => $features['groupe_primaire'] ?? 0.5,
        ]);

        // lastInsertId() renvoie 0 quand ON DUPLICATE KEY ne fait qu'une mise à jour
        $newId = (int)$this->pdo->lastInsertId();
        if ($newId > 0) {
            return $newId;
        }

        // Récupérer l'ID de la ligne existante
        $select = $this->pdo->prepare(
            'SELECT id FROM exercice_reference WHERE LOWER(nom) = LOWER(:nom) LIMIT 1'
        );
        $select->execute([':nom' => $nom]);
        return (int)$select->fetchColumn();
    }

    /**
     * Construit le contexte de flux HTTP pour file_get_contents.
     */
    private function buildContext(): mixed
    {
        return stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => [
                    'X-WorkoutX-Key: ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                'timeout' => $this->timeout,
            ],
        ]);
    }

    // =========================================================================
    // MÉTHODES EXISTANTES (conservées pour compatibilité ascendante)
    // =========================================================================

    /**
     * Récupère les exercices par nom depuis l'API WorkoutX (retourne les données brutes)
     *
     * @param string $name Nom de l'exercice à rechercher
     * @return array Données brutes de l'API
     * @throws Exception En cas d'erreur réseau ou API
     */
    public function fetchExercisesByName(string $name): array
    {
        $name = trim($name);
        if (empty($name)) {
            throw new Exception("Le nom de l'exercice ne peut pas être vide");
        }

        $url = $this->baseUrl . '/exercises?name=' . urlencode($name);

        try {
            $response = $this->makeRequest($url);

            if (is_array($response)) {
                return $response;
            } elseif (isset($response['exercises']) && is_array($response['exercises'])) {
                return $response['exercises'];
            } else {
                return [];
            }
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des données WorkoutX : ' . $e->getMessage());
        }
    }

    /**
     * Normalise les données brutes (API héritée – conservée pour compatibilité)
     *
     * @param array $apiData Données brutes retournées par fetchExercisesByName()
     * @return array Tableau ['intensite_calorique', 'equipement', 'difficulte', 'cible_musculaire']
     */
    public function normalizeFeatures(array $apiData): array
    {
        if (empty($apiData)) {
            return $this->getDefaultFeatures();
        }

        $exercise = isset($apiData[0]) && is_array($apiData[0]) ? $apiData[0] : $apiData;

        return $this->normalizeFromRaw($exercise);
    }

    /**
     * Effectue une requête HTTP GET vers l'API WorkoutX (méthode interne réutilisable)
     *
     * @param string $url URL complète à interroger
     * @return array Réponse décodée en JSON
     * @throws Exception En cas d'erreur
     */
    private function makeRequest(string $url): array
    {
        $response = @file_get_contents($url, false, $this->buildContext());

        if ($response === false) {
            throw new Exception("Impossible de contacter l'API WorkoutX");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Réponse JSON invalide de l'API");
        }

        return $decoded;
    }

    /**
     * Retourne les features par défaut en cas d'erreur API
     */
    private function getDefaultFeatures(): array
    {
        return [
            'intensite_calorique' => 0.5,
            'equipement'          => 0.3,
            'difficulte'          => 0.5,
            'cible_musculaire'    => 0.5,
        ];
    }
}
