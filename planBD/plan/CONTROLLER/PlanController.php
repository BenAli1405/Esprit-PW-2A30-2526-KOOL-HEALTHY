<?php

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../MODEL/Page.php';
include_once __DIR__ . '/../MODEL/PlanModel.php';

class PlanController
{
    private $pages;
    private $model;
    private $plans;
    private $message = '';
    private $messageType = 'success';

    public function __construct()
    {
        $this->model = new PlanModel();
        $this->pages = [
            new Page('backoffice', 'Backoffice', 'backoffice'),
            new Page('plan-adapte', 'Plan adapté', 'plan-adapte'),
            new Page('plan-nutritionnel', 'Plan nutritionnel', 'plan-nutritionnel')
        ];
    }

    public function listPages()
    {
        return $this->pages;
    }

    public function getPage($slug)
    {
        foreach ($this->pages as $page) {
            if ($page->getSlug() === $slug) {
                return $page;
            }
        }
        return null;
    }

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'create';
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $planData = [
                'nom' => trim($_POST['nom'] ?? ''),
                'objectif' => trim($_POST['objectif'] ?? ''),
                'utilisateur_id' => trim($_POST['utilisateur_id'] ?? ''),
                'duree' => trim($_POST['duree'] ?? ''),
                'preference' => trim($_POST['preference'] ?? ''),
                'allergies' => trim($_POST['allergies'] ?? ''),
            ];

            if ($action === 'delete' && $id > 0) {
                if ($this->model->delete($id)) {
                    $this->message = 'Plan supprimé avec succès.';
                } else {
                    $this->message = 'Impossible de supprimer le plan.';
                    $this->messageType = 'error';
                }
            } else {
                $errors = $this->validatePlanData($planData, $action, $id);
                if (count($errors) > 0) {
                    $this->message = 'Impossible : ' . implode(' ', $errors);
                    $this->messageType = 'error';
                } else {
                    if ($action === 'create') {
                        if ($this->model->create($planData)) {
                            $this->message = 'Plan ajouté avec succès.';
                        } else {
                            $this->message = 'Impossible d\'ajouter le plan.';
                            $this->messageType = 'error';
                        }
                    } elseif ($action === 'update' && $id > 0) {
                        if ($this->model->update($id, $planData)) {
                            $this->message = 'Plan mis à jour avec succès.';
                        } else {
                            $this->message = 'Impossible de mettre à jour le plan.';
                            $this->messageType = 'error';
                        }
                    }
                }
            }
        }

        $this->plans = $this->model->all();
    }

    private function validatePlanData(array $planData, string $action, int $id): array
    {
        $errors = [];
        $nom = $planData['nom'];
        $objectif = $planData['objectif'];
        $utilisateurId = $planData['utilisateur_id'];
        $duree = $planData['duree'];
        $preference = $planData['preference'];
        $allergies = $planData['allergies'];

        if (empty($nom) || strlen($nom) < 3 || strlen($nom) > 200) {
            $errors[] = 'Le nom du plan doit contenir entre 3 et 200 caractères.';
        }
        if (empty($objectif)) {
            $errors[] = 'Veuillez choisir un objectif.';
        }
        if (empty($utilisateurId)) {
            $errors[] = 'L\'identifiant utilisateur est obligatoire.';
        }
        if (empty($duree)) {
            $errors[] = 'La durée est obligatoire.';
        }
        if (empty($preference)) {
            $errors[] = 'La préférence alimentaire est obligatoire.';
        }
        if (empty($allergies)) {
            $errors[] = 'Le champ allergies est obligatoire.';
        }

        if (!empty($utilisateurId) && (!ctype_digit($utilisateurId) || (int)$utilisateurId < 1)) {
            $errors[] = 'L\'identifiant utilisateur doit être un nombre entier positif.';
        }

        if (!empty($duree)) {
            if (!ctype_digit($duree)) {
                $errors[] = 'La durée doit être un nombre valide.';
            } elseif ((int)$duree < 7) {
                $errors[] = 'La durée doit être au minimum de 7 jours.';
            }
        }

        $allPlans = $this->model->all();
        $isUpdate = ($action === 'update' && $id > 0);

        if (!empty($utilisateurId)) {
            foreach ($allPlans as $plan) {
                if ((string)$plan['utilisateur_id'] === (string)$utilisateurId && (!$isUpdate || (int)$plan['id'] !== $id)) {
                    $errors[] = 'Cet identifiant utilisateur possède déjà un plan (doit être unique).';
                    break;
                }
            }
        }

        return $errors;
    }

    public function render($slug)
    {
        $page = $this->getPage($slug);
        if ($page === null) {
            header('HTTP/1.0 404 Not Found');
            echo 'Page introuvable';
            return;
        }

        $plans = $this->plans;
        $message = $this->message;
        $messageType = $this->messageType;

        include __DIR__ . '/../VIEW/' . $page->getViewFile() . '.php';
    }
}
