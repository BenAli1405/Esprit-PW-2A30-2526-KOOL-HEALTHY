<?php
session_start();

include_once __DIR__ . '/../MODEL/MultiObjectiveModel.php';

class MultiObjectiveController
{
    private $model;

    public function __construct()
    {
        $this->model = new MultiObjectiveModel();
    }

    public function handleAjaxRequest()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Requête invalide']);
            exit;
        }

        $action = $input['action'] ?? '';

        if ($action === 'update_weights') {
            $weights = $input['weights'] ?? [];
            
            $validWeights = [
                'perte_poids' => 0,
                'plaisir' => 0,
                'budget' => 0,
                'rapidite' => 0,
                'ecologie' => 0
            ];

            $total = 0;
            foreach ($validWeights as $key => $val) {
                if (isset($weights[$key])) {
                    $w = (int)$weights[$key];
                    if ($w >= 0 && $w <= 100) {
                        $validWeights[$key] = $w;
                        $total += $w;
                    }
                }
            }

            if ($total <= 0) {
                // Si tout est à 0, on met une répartition équitable
                $validWeights = ['perte_poids' => 20, 'plaisir' => 20, 'budget' => 20, 'rapidite' => 20, 'ecologie' => 20];
            }

            $_SESSION['multi_objective_weights'] = $validWeights;
            echo json_encode(['success' => true, 'weights' => $validWeights]);
            exit;
        }

        if ($action === 'get_recommendation') {
            $planId = isset($input['plan_id']) ? (int)$input['plan_id'] : 0;
            if ($planId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Plan non spécifié']);
                exit;
            }

            $weights = $_SESSION['multi_objective_weights'] ?? [
                'perte_poids' => 80,
                'plaisir' => 30,
                'budget' => 60,
                'rapidite' => 60,
                'ecologie' => 30
            ];

            $repasList = $this->model->getAllRepasForPlan($planId);
            if (empty($repasList)) {
                echo json_encode(['success' => true, 'recommendations' => [], 'weights' => $weights]);
                exit;
            }

            // Meilleur repas du plan (recommandation principale)
            $planRecommendations = $this->model->recommanderRepas($repasList, $weights);
            $bestPlanMeal = $planRecommendations[0] ?? null;

            $finalRecs = [];
            if ($bestPlanMeal) {
                $finalRecs[] = $bestPlanMeal;
                
                // On récupère tous les repas uniques de la base globale pour trouver des alternatives
                $globalRepas = $this->model->getAllGlobalRepas();
                $globalRecommendations = $this->model->recommanderRepas($globalRepas, $weights);

                // On liste les noms déjà présents dans le plan pour ne pas les proposer en alternative
                $nomsDansPlan = array_map(function($r) { 
                    return strtolower(trim($r['nom_recette'])); 
                }, $repasList);

                // On ajoute 2 alternatives (maximum) de la base globale
                foreach ($globalRecommendations as $rec) {
                    $nom = strtolower(trim($rec['nom_recette']));
                    if (!in_array($nom, $nomsDansPlan)) {
                        $finalRecs[] = $rec;
                        $nomsDansPlan[] = $nom; // Pour éviter les doublons dans les alternatives
                    }
                    if (count($finalRecs) >= 3) {
                        break;
                    }
                }
            }

            echo json_encode(['success' => true, 'recommendations' => $finalRecs, 'weights' => $weights]);
            exit;
        }

        if ($action === 'update_multicriteria' || $action === 'save_criteres') {
            $repasId = isset($input['repas_id']) ? (int)$input['repas_id'] : 0;
            $prix = isset($input['prix']) ? (float)$input['prix'] : 5.0;
            $temps = isset($input['temps_preparation']) ? (int)$input['temps_preparation'] : 20;
            $ecoScore = isset($input['eco_score']) ? strtoupper(trim($input['eco_score'])) : 'C';
            $notePlaisir = isset($input['note_plaisir']) ? (int)$input['note_plaisir'] : 7;

            // Validations
            if ($repasId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID repas invalide.']);
                exit;
            }
            if ($prix < 0 || $prix > 50) {
                echo json_encode(['success' => false, 'message' => 'Le prix doit être entre 0 et 50.']);
                exit;
            }
            if ($temps < 1 || $temps > 180) {
                echo json_encode(['success' => false, 'message' => 'Le temps doit être entre 1 et 180 min.']);
                exit;
            }
            if (!in_array($ecoScore, ['A', 'B', 'C', 'D', 'E'])) {
                echo json_encode(['success' => false, 'message' => "L'éco-score doit être A, B, C, D ou E."]);
                exit;
            }
            if ($notePlaisir < 1 || $notePlaisir > 10) {
                echo json_encode(['success' => false, 'message' => 'La note de plaisir doit être entre 1 et 10.']);
                exit;
            }

            $success = $this->model->saveCriteresToSession($repasId, [
                'prix' => $prix,
                'temps_preparation' => $temps,
                'eco_score' => $ecoScore,
                'note_plaisir' => $notePlaisir
            ]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Critères mis à jour avec succès en session.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour des critères en session.']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Action inconnue']);
        exit;
    }
}

// Point d'entrée direct pour AJAX
if (basename($_SERVER['PHP_SELF']) === 'MultiObjectiveController.php') {
    $controller = new MultiObjectiveController();
    $controller->handleAjaxRequest();
}
