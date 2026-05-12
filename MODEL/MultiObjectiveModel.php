<?php

include_once __DIR__ . '/Database.php';

class MultiObjectiveModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getAllRepasForPlan(int $planId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM repas WHERE plan_id = ? ORDER BY date, heure_prevue');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function getAllGlobalRepas(): array
    {
        // Retourne une liste globale de repas (sans duplication excessive)
        $stmt = $this->pdo->query('SELECT id, nom_recette, calories_consommees, NULL AS prix, NULL AS temps_preparation, NULL AS eco_score, NULL AS note_plaisir FROM repas GROUP BY nom_recette ORDER BY COUNT(*) DESC');
        return $stmt->fetchAll();
    }

    public function saveCriteresToSession(int $repasId, array $criteres): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['multi_objective_criteria']) || !is_array($_SESSION['multi_objective_criteria'])) {
            $_SESSION['multi_objective_criteria'] = [];
        }
        $_SESSION['multi_objective_criteria'][(int)$repasId] = [
            'prix' => isset($criteres['prix']) ? (float)$criteres['prix'] : null,
            'temps_preparation' => isset($criteres['temps_preparation']) ? (int)$criteres['temps_preparation'] : null,
            'eco_score' => isset($criteres['eco_score']) ? strtoupper(trim($criteres['eco_score'])) : null,
            'note_plaisir' => isset($criteres['note_plaisir']) ? (int)$criteres['note_plaisir'] : null,
        ];
        return true;
    }

    private function loadCriteresFromSession(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['multi_objective_criteria'] ?? [];
    }

    private function ecoScoreToNumeric($score): int
    {
        $map = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1];
        $s = strtoupper(trim((string)$score));
        return $map[$s] ?? 3;
    }

    public function recommanderRepas(array $repasList, array $weights): array
    {
        // weights keys: perte_poids, plaisir, budget, rapidite, ecologie (0-100)
        $criteriaSession = $this->loadCriteresFromSession();

        $results = [];
        foreach ($repasList as $r) {
            $id = (int)($r['id'] ?? 0);
            $nom = $r['nom_recette'] ?? ($r['titre'] ?? 'repas');

            // Fetch criteria from session or defaults
            $crit = $criteriaSession[$id] ?? [
                'prix' => $r['prix'] ?? 5.0,
                'temps_preparation' => $r['temps_preparation'] ?? 20,
                'eco_score' => $r['eco_score'] ?? 'C',
                'note_plaisir' => $r['note_plaisir'] ?? 7
            ];

            $prix = (float)($crit['prix'] ?? 5.0);
            $temps = (int)($crit['temps_preparation'] ?? 20);
            $eco = $this->ecoScoreToNumeric($crit['eco_score'] ?? 'C');
            $plaisir = (int)($crit['note_plaisir'] ?? 7);

            // calories (if present) — lower is better for perte_poids
            $cal = isset($r['calories_consommees']) && is_numeric($r['calories_consommees']) ? (int)$r['calories_consommees'] : 2000;

            // Normalize and score components (simple heuristic)
            $scorePertePoids = 1.0 - (min(max($cal, 0), 4000) / 4000.0); // 0..1 (lower calories => higher)
            $scorePlaisir = min(max($plaisir, 1), 10) / 10.0; // 0..1
            $scoreBudget = 1.0 - (min(max($prix, 0), 50) / 50.0); // 0..1 (cheaper better)
            $scoreRapidite = 1.0 - (min(max($temps, 1), 180) / 180.0); // 0..1 (faster better)
            $scoreEco = ($eco - 1) / 4.0; // map 1..5 to 0..1 (A highest)

            // Apply weights (normalize weights sum)
            $wSum = max(array_sum($weights), 1);
            $weighted = (
                ($weights['perte_poids'] ?? 0) * $scorePertePoids +
                ($weights['plaisir'] ?? 0) * $scorePlaisir +
                ($weights['budget'] ?? 0) * $scoreBudget +
                ($weights['rapidite'] ?? 0) * $scoreRapidite +
                ($weights['ecologie'] ?? 0) * $scoreEco
            ) / $wSum;

            $results[] = array_merge($r, [
                'multi_score' => round($weighted, 4),
                'computed' => [
                    'calories' => $cal,
                    'prix' => $prix,
                    'temps' => $temps,
                    'eco_numeric' => $eco,
                    'note_plaisir' => $plaisir
                ]
            ]);
        }

        usort($results, function($a, $b){
            return $b['multi_score'] <=> $a['multi_score'];
        });

        return $results;
    }
}
