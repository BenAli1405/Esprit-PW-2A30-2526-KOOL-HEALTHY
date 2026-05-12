<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../MODEL/JumeauModel.php';
include_once __DIR__ . '/../MODEL/PlanModel.php';

class JumeauController
{
    private $jumeauModel;
    private $planModel;

    public function __construct()
    {
        $this->jumeauModel = new JumeauModel();
        $this->planModel = new PlanModel();
    }

    public function handleAjaxRequest(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(false, 'Méthode non autorisée.', 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->sendJson(false, 'Format JSON invalide.', 400);
            return;
        }

        $action = isset($data['action']) ? strip_tags(trim($data['action'])) : '';
        if (empty($action)) {
            $this->sendJson(false, "L'action est obligatoire.", 400);
            return;
        }

        switch ($action) {
            case 'get_twin_stats':  $this->handleGetStats($data); break;
            case 'get_prediction':  $this->handleGetPrediction($data); break;
            case 'simulate_ecart':  $this->handleSimulateEcart($data); break;
            default: $this->sendJson(false, 'Action inconnue.', 400); break;
        }
    }

    private function handleGetStats(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err) { $this->sendJson(false, $err, 400); return; }

        $plan = $this->planModel->find((int)$data['plan_id']);
        if (!$plan) { $this->sendJson(false, 'Plan introuvable.', 404); return; }

        $stats = $this->jumeauModel->getFullStats($plan);
        $this->sendJson(true, 'OK', 200, ['stats' => $stats]);
    }

    private function handleGetPrediction(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err) { $this->sendJson(false, $err, 400); return; }

        $plan = $this->planModel->find((int)$data['plan_id']);
        if (!$plan) { $this->sendJson(false, 'Plan introuvable.', 404); return; }

        $nbJours = isset($data['nb_jours']) ? (int)$data['nb_jours'] : 7;
        if ($nbJours < 1) $nbJours = 1;
        if ($nbJours > 90) $nbJours = 90;

        $stats = $this->jumeauModel->getFullStats($plan, $nbJours);
        $forecastTotal = isset($stats['forecast_total']) ? (float)$stats['forecast_total'] : 0.0;
        $predictions = $stats['predictions_legacy'] ?? [];
        $this->sendJson(true, 'OK', 200, ['forecast' => $forecastTotal, 'predictions' => $predictions]);
    }

    private function handleSimulateEcart(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err) { $this->sendJson(false, $err, 400); return; }

        if (!isset($data['ecart']) || !is_numeric($data['ecart'])) {
            $this->sendJson(false, "L'écart calorique est obligatoire (nombre).", 400);
            return;
        }
        $ecart = (int)$data['ecart'];
        if ($ecart < -2000 || $ecart > 5000) {
            $this->sendJson(false, "L'écart doit être entre -2000 et +5000 kcal.", 400);
            return;
        }

        $plan = $this->planModel->find((int)$data['plan_id']);
        if (!$plan) { $this->sendJson(false, 'Plan introuvable.', 404); return; }

        $result = $this->jumeauModel->simulerEcart($plan, $ecart);
        $this->sendJson(true, 'OK', 200, ['simulation' => $result]);
    }

    private function validatePlanId(array $data): ?string
    {
        if (!isset($data['plan_id'])) return 'Le plan_id est obligatoire.';
        if (!is_numeric($data['plan_id']) || (int)$data['plan_id'] != $data['plan_id']) return 'Le plan_id doit être un entier.';
        if ((int)$data['plan_id'] <= 0) return 'Le plan_id doit être > 0.';
        return null;
    }

    private function sendJson(bool $success, string $msg, int $code = 200, array $data = []): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['success' => $success, 'message' => $msg], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$controller = new JumeauController();
$controller->handleAjaxRequest();
