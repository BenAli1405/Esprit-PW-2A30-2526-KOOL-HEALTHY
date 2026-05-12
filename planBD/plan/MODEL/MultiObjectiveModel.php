<?php

require_once __DIR__ . '/RepasModel.php';

class MultiObjectiveModel
{
    private $repasModel;

    public function __construct()
    {
        $this->repasModel = new RepasModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getAllRepasForPlan($planId)
    {
        return $this->repasModel->getByPlanId($planId);
    }

    public function getAllGlobalRepas()
    {
        return $this->repasModel->all();
    }

    public function getCriteresFromSession($repasId)
    {
        if (isset($_SESSION['repas_criteres'][$repasId])) {
            return $_SESSION['repas_criteres'][$repasId];
        }
        
        // Génération de valeurs pseudo-aléatoires déterministes basées sur l'ID
        // pour que chaque plat ait des statistiques différentes par défaut.
        $hash = md5((string)$repasId);
        $prixParDefaut = 5.0 + (hexdec(substr($hash, 0, 1)) % 15); // 5 à 19 €
        $tempsParDefaut = 10 + (hexdec(substr($hash, 1, 1)) % 30); // 10 à 39 min
        $ecoScores = ['A', 'B', 'C', 'D', 'E'];
        $ecoScoreParDefaut = $ecoScores[hexdec(substr($hash, 2, 1)) % 5];
        $plaisirParDefaut = 5 + (hexdec(substr($hash, 3, 1)) % 5); // 5 à 9

        return [
            'prix' => (float)$prixParDefaut,
            'temps_preparation' => (int)$tempsParDefaut,
            'eco_score' => $ecoScoreParDefaut,
            'note_plaisir' => (int)$plaisirParDefaut
        ];
    }

    public function saveCriteresToSession($repasId, $data)
    {
        if (!isset($_SESSION['repas_criteres'])) {
            $_SESSION['repas_criteres'] = [];
        }
        
        $criteres = $this->getCriteresFromSession($repasId);
        if (isset($data['prix'])) $criteres['prix'] = (float)$data['prix'];
        if (isset($data['temps_preparation'])) $criteres['temps_preparation'] = (int)$data['temps_preparation'];
        if (isset($data['eco_score'])) $criteres['eco_score'] = strtoupper(trim($data['eco_score']));
        if (isset($data['note_plaisir'])) $criteres['note_plaisir'] = (int)$data['note_plaisir'];

        $_SESSION['repas_criteres'][$repasId] = $criteres;
        return true;
    }

    public function normaliserScores($repasList)
    {
        $result = [];
        foreach ($repasList as $repas) {
            $id = $repas['id'];
            $criteres = $this->getCriteresFromSession($id);
            $calories = (int)($repas['calories_consommees'] ?? 0);
            
            // Calculer les scores normalisés (0 à 100)
            $scoreCalories = $this->calculerScoreCalories($calories);
            $scorePrix = $this->calculerScorePrix($criteres['prix']);
            $scoreTemps = $this->calculerScoreTemps($criteres['temps_preparation']);
            $scoreEco = $this->calculerScoreEco($criteres['eco_score']);
            $scorePlaisir = $this->calculerScorePlaisir($criteres['note_plaisir']);

            $repas['_simulations'] = [
                'calories' => $calories,
                'prix' => $criteres['prix'],
                'temps' => $criteres['temps_preparation'],
                'eco' => $criteres['eco_score'],
                'plaisir' => $criteres['note_plaisir']
            ];

            $repas['_scores'] = [
                'perte_poids' => $scoreCalories,
                'plaisir' => $scorePlaisir,
                'budget' => $scorePrix,
                'rapidite' => $scoreTemps,
                'ecologie' => $scoreEco
            ];

            $result[] = $repas;
        }
        return $result;
    }

    public function recommanderRepas($repasList, $poids)
    {
        $repasEnrichis = $this->normaliserScores($repasList);

        $totalPoids = array_sum($poids);
        if ($totalPoids == 0) {
            $poids = ['perte_poids' => 20, 'plaisir' => 20, 'budget' => 20, 'rapidite' => 20, 'ecologie' => 20];
            $totalPoids = 100;
        }

        foreach ($repasEnrichis as &$repas) {
            $s = $repas['_scores'];
            $composite = (
                $s['perte_poids'] * ($poids['perte_poids'] ?? 0) +
                $s['plaisir'] * ($poids['plaisir'] ?? 0) +
                $s['budget'] * ($poids['budget'] ?? 0) +
                $s['rapidite'] * ($poids['rapidite'] ?? 0) +
                $s['ecologie'] * ($poids['ecologie'] ?? 0)
            ) / $totalPoids;

            $repas['score_composite'] = round($composite);
        }

        usort($repasEnrichis, function ($a, $b) {
            return $b['score_composite'] <=> $a['score_composite'];
        });

        return $repasEnrichis;
    }

    private function calculerScoreCalories($calories) {
        if ($calories <= 0) return 0;
        $diff = abs(500 - $calories);
        $score = 100 - ($diff * 0.2);
        return max(0, min(100, round($score)));
    }

    private function calculerScorePrix($prix) {
        $score = 100 - ($prix * 5);
        return max(0, min(100, round($score)));
    }

    private function calculerScoreTemps($temps) {
        $score = 100 - ($temps * 1.5);
        return max(0, min(100, round($score)));
    }

    private function calculerScoreEco($ecoScore) {
        $map = ['A' => 100, 'B' => 80, 'C' => 60, 'D' => 40, 'E' => 20];
        return $map[$ecoScore] ?? 0;
    }

    private function calculerScorePlaisir($plaisir) {
        return max(0, min(100, $plaisir * 10));
    }
}
