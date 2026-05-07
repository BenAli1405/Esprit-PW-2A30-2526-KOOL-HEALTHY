<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Modèle KNN (K-Nearest Neighbors) — 6 caractéristiques, distance pondérée
 *
 * Caractéristiques utilisées :
 *   1. intensite_calorique  (poids 0.8)  — effort calorique
 *   2. equipement           (poids 1.0)  — type de matériel
 *   3. difficulte           (poids 1.0)  — niveau requis
 *   4. cible_musculaire     (poids 2.5)  — groupe musculaire ciblé ★ priorité max
 *   5. type_mouvement       (poids 1.5)  — compound / isolation / cardio…
 *   6. groupe_primaire      (poids 2.0)  — muscle principal (granularité fine)
 *
 * Distance pondérée : sqrt( Σ w_i × (v1_i − v2_i)² )
 */
class KnnModel
{
    private $pdo;

    /**
     * Poids associés à chaque dimension du vecteur.
     * Ordre identique à celui des vecteurs construits dans getSimilarExercises().
     */
    private const WEIGHTS = [
        'intensite_calorique' => 0.8,
        'equipement'          => 1.0,
        'difficulte'          => 1.0,
        'cible_musculaire'    => 2.5,
        'type_mouvement'      => 1.5,
        'groupe_primaire'     => 2.0,
    ];

    public function __construct()
    {
        $this->pdo = (new \Database())->getConnection();
    }

    // =========================================================================
    // ALGORITHME KNN
    // =========================================================================

    /**
     * Récupère les K exercices les plus similaires à un exercice de référence.
     * Utilise une distance euclidienne pondérée sur 6 caractéristiques.
     *
     * @param int $idSource ID de l'exercice source dans exercice_reference
     * @param int $k        Nombre de voisins à retourner (défaut : 5)
     * @return array        Tableau des exercices similaires triés par distance croissante
     * @throws Exception    Si l'exercice source n'existe pas
     */
    public function getSimilarExercises(int $idSource, int $k = 5): array
    {
        $k = max(1, $k);

        // 1. Récupérer l'exercice source (6 features)
        $source = $this->getReferenceExercise($idSource);
        if (!$source) {
            throw new Exception("L'exercice source n'existe pas dans la base de référence.");
        }

        $sourceVector = $this->buildVector($source);

        // 2. Récupérer tous les autres exercices de référence
        $stmt = $this->pdo->prepare('
            SELECT id, nom,
                   intensite_calorique, equipement, difficulte,
                   cible_musculaire, type_mouvement, groupe_primaire,
                   video_url
            FROM exercice_reference
            WHERE id != :id
        ');
        $stmt->execute(['id' => $idSource]);
        $allOthers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Calculer les distances pondérées
        $distances = [];
        foreach ($allOthers as $target) {
            $targetVector       = $this->buildVector($target);
            $target['distance'] = round($this->weightedDistance($sourceVector, $targetVector), 4);
            $distances[]        = $target;
        }

        // 4. Trier par distance croissante et retourner les K premiers
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return array_slice($distances, 0, $k);
    }

    /**
     * Construit le vecteur de features à partir d'une ligne de la base.
     * Les clés absentes (anciennes lignes sans les nouvelles colonnes) valent 0.5.
     */
    private function buildVector(array $row): array
    {
        return [
            'intensite_calorique' => (float)($row['intensite_calorique'] ?? 0.5),
            'equipement'          => (float)($row['equipement']          ?? 0.5),
            'difficulte'          => (float)($row['difficulte']          ?? 0.5),
            'cible_musculaire'    => (float)($row['cible_musculaire']    ?? 0.5),
            'type_mouvement'      => (float)($row['type_mouvement']      ?? 0.5),
            'groupe_primaire'     => (float)($row['groupe_primaire']      ?? 0.5),
        ];
    }

    /**
     * Calcule la distance euclidienne pondérée entre deux vecteurs associatifs.
     * d = sqrt( Σ w_k × (v1_k − v2_k)² )
     */
    private function weightedDistance(array $v1, array $v2): float
    {
        $sumSquares = 0.0;
        foreach (self::WEIGHTS as $key => $weight) {
            $diff        = ($v1[$key] ?? 0.5) - ($v2[$key] ?? 0.5);
            $sumSquares += $weight * $diff * $diff;
        }
        return sqrt($sumSquares);
    }

    // =========================================================================
    // REQUÊTES DE LECTURE
    // =========================================================================

    /**
     * Retourne tous les exercices de référence (pour le select de la vue KNN).
     */
    public function getAllReferenceExercises(): array
    {
        $stmt = $this->pdo->query('
            SELECT id, nom,
                   intensite_calorique, equipement, difficulte,
                   cible_musculaire, type_mouvement, groupe_primaire,
                   video_url
            FROM exercice_reference
            ORDER BY nom ASC
        ');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne un exercice de référence par son ID (6 features + video_url).
     */
    public function getReferenceExercise(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, nom,
                   intensite_calorique, equipement, difficulte,
                   cible_musculaire, type_mouvement, groupe_primaire,
                   video_url
            FROM exercice_reference
            WHERE id = :id
        ');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retourne un exercice de référence par son nom (insensible à la casse).
     */
    public function getExerciceByNom(string $nom): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, nom,
                   intensite_calorique, equipement, difficulte,
                   cible_musculaire, type_mouvement, groupe_primaire,
                   video_url
            FROM exercice_reference
            WHERE LOWER(nom) = LOWER(:nom)
        ');
        $stmt->execute(['nom' => $nom]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // =========================================================================
    // CRUD (administration)
    // =========================================================================

    public function createReference(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO exercice_reference
                (nom, intensite_calorique, equipement, difficulte,
                 cible_musculaire, type_mouvement, groupe_primaire)
            VALUES
                (:nom, :intensite, :equipement, :difficulte,
                 :cible, :type_mvt, :groupe)
        ');
        return $stmt->execute([
            'nom'        => $data['nom'],
            'intensite'  => $data['intensite_calorique'],
            'equipement' => $data['equipement'],
            'difficulte' => $data['difficulte'],
            'cible'      => $data['cible_musculaire'],
            'type_mvt'   => $data['type_mouvement']  ?? 0.5,
            'groupe'     => $data['groupe_primaire']  ?? 0.5,
        ]);
    }

    public function updateReference(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE exercice_reference
            SET nom                 = :nom,
                intensite_calorique = :intensite,
                equipement          = :equipement,
                difficulte          = :difficulte,
                cible_musculaire    = :cible,
                type_mouvement      = :type_mvt,
                groupe_primaire     = :groupe
            WHERE id = :id
        ');
        return $stmt->execute([
            'id'         => $id,
            'nom'        => $data['nom'],
            'intensite'  => $data['intensite_calorique'],
            'equipement' => $data['equipement'],
            'difficulte' => $data['difficulte'],
            'cible'      => $data['cible_musculaire'],
            'type_mvt'   => $data['type_mouvement']  ?? 0.5,
            'groupe'     => $data['groupe_primaire']  ?? 0.5,
        ]);
    }

    public function deleteReference(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM exercice_reference WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function referenceExists(string $nom, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM exercice_reference WHERE nom = :nom';
        $params = ['nom' => $nom];

        if ($excludeId !== null) {
            $sql         .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
