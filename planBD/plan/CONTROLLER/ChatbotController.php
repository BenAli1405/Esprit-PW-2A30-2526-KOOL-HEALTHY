<?php

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../MODEL/ChatbotModel.php';

class ChatbotController
{
    private $chatbotModel;

    public function __construct()
    {
        $this->chatbotModel = new ChatbotModel();
    }

    public function handleAjaxRequest(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(false, 'Méthode non autorisée.', 405);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

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
            case 'send_message':
                $this->handleSendMessage($data);
                break;
            case 'get_historique':
                $this->handleGetHistorique($data);
                break;
            case 'analyze_notes':
                $this->handleAnalyzeNotes($data);
                break;
            default:
                $this->sendJson(false, 'Action inconnue.', 400);
                break;
        }
    }

    private function handleSendMessage(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err !== null) { $this->sendJson(false, $err, 400); return; }
        $planId = (int)$data['plan_id'];

        if (!isset($data['message']) || trim($data['message']) === '') {
            $this->sendJson(false, 'Le message ne peut pas être vide.', 400);
            return;
        }

        $message = strip_tags(trim($data['message']));
        if (mb_strlen($message) === 0) {
            $this->sendJson(false, 'Le message ne peut pas être vide après nettoyage.', 400);
            return;
        }
        if (mb_strlen($message) > 1000) {
            $this->sendJson(false, 'Le message ne peut pas dépasser 1000 caractères.', 400);
            return;
        }

        $result   = $this->chatbotModel->traiterMessage($planId, $message);
        $reponse  = $result['reponse'];
        $sentiment = $result['sentiment'];
        $intent   = $result['intent'];

        $this->sendJson(true, 'OK', 200, [
            'reponse'   => $reponse,
            'sentiment' => $sentiment,
            'intent'    => $intent,
            'saved'     => true,
        ]);
    }

    private function handleGetHistorique(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err !== null) { $this->sendJson(false, $err, 400); return; }
        $planId = (int)$data['plan_id'];

        $limit = isset($data['limit']) ? (int)$data['limit'] : 20;
        if ($limit < 1) $limit = 1;
        if ($limit > 50) $limit = 50;

        $historique = $this->chatbotModel->recupererHistorique($planId);
        $this->sendJson(true, 'Historique récupéré.', 200, ['historique' => $historique]);
    }

    private function handleAnalyzeNotes(array $data): void
    {
        $err = $this->validatePlanId($data);
        if ($err !== null) { $this->sendJson(false, $err, 400); return; }
        $planId = (int)$data['plan_id'];

        $analyse = $this->chatbotModel->analyserNotesPlan($planId);
        $this->sendJson(true, 'Analyse effectuée.', 200, ['analyse' => $analyse]);
    }

    private function validatePlanId(array $data): ?string
    {
        if (!isset($data['plan_id'])) {
            return 'Le plan_id est obligatoire.';
        }
        $planId = $data['plan_id'];
        if (!is_numeric($planId) || (int)$planId != $planId) {
            return 'Le plan_id doit être un nombre entier.';
        }
        if ((int)$planId <= 0) {
            return 'Le plan_id doit être supérieur à 0.';
        }
        return null;
    }

    private function sendJson(bool $success, string $message, int $httpCode = 200, array $data = []): void
    {
        http_response_code($httpCode);
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Point d'entrée direct (appels AJAX)
$controller = new ChatbotController();
$controller->handleAjaxRequest();
