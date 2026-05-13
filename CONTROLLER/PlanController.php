<?php

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../MODEL/Page.php';
include_once __DIR__ . '/../MODEL/PlanModel.php';
include_once __DIR__ . '/../MODEL/RepasModel.php';

class PlanController
{
    private $pages;
    private $model;
    private $repasModel;
    private $plans;
    private $repasList;
    private $message = '';
    private $messageType = 'success';

    public function __construct()
    {
        $this->model = new PlanModel();
        $this->repasModel = new RepasModel();
        $this->pages = [
            new Page('plan-backoffice', 'Plan Backoffice', 'plan-backoffice'),
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentUserId = $_SESSION['utilisateur']['id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionType = $_POST['action_type'] ?? 'plan'; // 'plan' or 'repas'
            $action = $_POST['action'] ?? 'create';
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

            if ($actionType === 'repas') {
                $repasData = [
                    'plan_id'             => trim($_POST['plan_id'] ?? ''),
                    'nom_recette'         => trim($_POST['nom_recette'] ?? ''),
                    'date'                => trim($_POST['date'] ?? ''),
                    'type_repas'          => trim($_POST['type_repas'] ?? ''),
                    'statut'              => trim($_POST['statut'] ?? 'prevu'),
                    'calories_consommees' => trim($_POST['calories_consommees'] ?? ''),
                    'heure_prevue'        => trim($_POST['heure_prevue'] ?? ''),
                    'heure_reelle'        => trim($_POST['heure_reelle'] ?? ''),
                    'notes'               => trim($_POST['notes'] ?? ''),
                ];

                if ($action === 'delete' && $id > 0) {
                    // Ensure user owns the plan to which this repas belongs (if logged in)
                    $canDelete = true;
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                    $repasItem = $this->repasModel->find($id);
                    if ($repasItem && $currentUserId) {
                        $plan = $this->model->find((int)$repasItem['plan_id']);
                        if (!$plan || (int)$plan['utilisateur_id'] !== (int)$currentUserId) {
                            $canDelete = false;
                        }
                    }
                    if ($canDelete && $this->repasModel->delete($id)) {
                        $this->message = 'Repas supprimé avec succès.';
                    } else {
                        $this->message = 'Impossible de supprimer le repas.';
                        $this->messageType = 'error';
                    }
                } else {
                    $errors = $this->validateRepasData($repasData);
                    if (count($errors) > 0) {
                        $this->message = 'Impossible : ' . implode(' ', $errors);
                        $this->messageType = 'error';
                    } else {
                        $repasData['heure_prevue']        = empty($repasData['heure_prevue']) ? null : $repasData['heure_prevue'];
                        $repasData['heure_reelle']        = empty($repasData['heure_reelle']) ? null : $repasData['heure_reelle'];
                        $repasData['calories_consommees'] = $repasData['calories_consommees'] === '' ? null : (int)$repasData['calories_consommees'];

                        if ($action === 'create') {
                            if ($this->repasModel->create($repasData)) {
                                $this->message = 'Repas ajouté avec succès.';
                            } else {
                                $this->message = 'Impossible d\'ajouter le repas.';
                                $this->messageType = 'error';
                            }
                        } elseif ($action === 'update' && $id > 0) {
                            if ($this->repasModel->update($id, $repasData)) {
                                $this->message = 'Repas mis à jour avec succès.';
                            } else {
                                $this->message = 'Impossible de mettre à jour le repas.';
                                $this->messageType = 'error';
                            }
                        }
                    }
                }
            } else {
                $planData = [
                    'nom' => trim($_POST['nom'] ?? ''),
                    'objectif' => trim($_POST['objectif'] ?? ''),
                    // Do not trust client; use session user id when available
                    'utilisateur_id' => $currentUserId ? (int)$currentUserId : trim($_POST['utilisateur_id'] ?? ''),
                    'duree' => trim($_POST['duree'] ?? ''),
                    'preference' => trim($_POST['preference'] ?? ''),
                    'allergies' => trim($_POST['allergies'] ?? ''),
                ];

                if ($action === 'delete' && $id > 0) {
                    // Ensure user owns the plan (if logged in) before deleting
                    $canDelete = true;
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                    if ($currentUserId) {
                        $plan = $this->model->find($id);
                        if (!$plan || (int)$plan['utilisateur_id'] !== (int)$currentUserId) {
                            $canDelete = false;
                        }
                    }
                    if ($canDelete && $this->model->delete($id)) {
                        header('Location: plan.php?page=plan-nutritionnel');
                        exit;
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
                                $newId = $this->model->getLastInsertId();
                                // Generate default meals for the new plan
                                $dureeForGen = isset($planData['duree']) && ctype_digit((string)$planData['duree']) ? (int)$planData['duree'] : 7;
                                $this->repasModel->generateForPlan((int)$newId, $dureeForGen);
                                header('Location: plan.php?page=plan-adapte&id=' . $newId);
                                exit;
                            } else {
                                $err = $this->model->getLastError();
                                $friendly = 'Impossible d\'ajouter le plan.';
                                if ($err) {
                                    // Detect duplicate-key unique constraint
                                    if (stripos($err, 'Integrity constraint violation') !== false || stripos($err, 'Duplicate entry') !== false) {
                                        $friendly = 'Impossible : un plan existe déjà pour cet utilisateur (contrainte unique en base). Pour autoriser plusieurs plans par utilisateur, exécutez la commande SQL suivante dans votre base de données :\n\nALTER TABLE `plan` DROP INDEX `uniq_plan_utilisateur`;\n\nEnsuite réessayez.';
                                    } else {
                                        $friendly = 'Impossible d\'ajouter le plan : ' . $err;
                                    }
                                }
                                $this->message = $friendly;
                                $this->messageType = 'error';
                            }
                        } elseif ($action === 'update' && $id > 0) {
                            // Ensure ownership when updating
                            $canUpdate = true;
                            if (session_status() === PHP_SESSION_NONE) session_start();
                            $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                            if ($currentUserId) {
                                $existingPlan = $this->model->find($id);
                                if (!$existingPlan || (int)$existingPlan['utilisateur_id'] !== (int)$currentUserId) {
                                    $canUpdate = false;
                                }
                            }
                            if ($canUpdate && $this->model->update($id, $planData)) {
                                header('Location: plan.php?page=plan-adapte&id=' . $id);
                                exit;
                            } else {
                                $this->message = 'Impossible de mettre à jour le plan.';
                                $this->messageType = 'error';
                            }
                        }
                    }
                }
            }
        }

        // Load plans filtered by user when available
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
        if ($currentUserId) {
            $this->plans = $this->model->allByUser((int)$currentUserId);
            $planIds = array_map(function($p){ return (int)$p['id']; }, $this->plans);
            try {
                $this->repasList = $this->repasModel->getByPlanIds($planIds);
            } catch (Exception $e) {
                $this->repasList = [];
            }
        } else {
            $this->plans = $this->model->all();
            try {
                $this->repasList = $this->repasModel->all();
            } catch (Exception $e) {
                $this->repasList = [];
            }
        }
    }

    private function validateRepasData(array $data): array
    {
        $errors = [];
        $validTypes   = ['petit_dejeuner', 'dejeuner', 'diner', 'collation'];
        $validStatuts = ['prevu', 'consomme', 'annule'];

        if (empty($data['plan_id']) || !ctype_digit((string)$data['plan_id']) || (int)$data['plan_id'] <= 0) {
            $errors[] = "Le plan est obligatoire (nombre entier > 0).";
        } else {
            // Vérifier que le plan existe réellement en base
            $planId = (int)$data['plan_id'];
            $planExists = $this->model->find($planId);
            if (!$planExists) {
                $errors[] = "Le Plan ID " . $planId . " n'existe pas. Veuillez saisir un ID de plan valide.";
            } else {
                // Si utilisateur connecté, vérifier que le plan appartient à lui
                if (session_status() === PHP_SESSION_NONE) session_start();
                $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                if ($currentUserId && (int)$planExists['utilisateur_id'] !== (int)$currentUserId) {
                    $errors[] = "Vous ne pouvez pas ajouter ou modifier un repas pour un plan qui n'appartient pas à votre compte.";
                }
            }
        }
        if (empty($data['nom_recette'])) {
            $errors[] = "Le nom de la recette est obligatoire.";
        } elseif (strlen($data['nom_recette']) > 255) {
            $errors[] = "Le nom de la recette ne peut pas dépasser 255 caractères.";
        }
        if (empty($data['date'])) {
            $errors[] = "La date est obligatoire.";
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $data['date']);
            if (!$d) {
                $errors[] = "Format de date invalide (AAAA-MM-JJ).";
            } else {
                $farPast = new DateTime('-5 years');
                if ($d < $farPast) {
                    $errors[] = "La date ne peut pas être dans un passé trop lointain (plus de 5 ans).";
                }
            }
        }
        if (empty($data['type_repas']) || !in_array($data['type_repas'], $validTypes, true)) {
            $errors[] = "Le type de repas est obligatoire.";
        }
        if (empty($data['statut']) || !in_array($data['statut'], $validStatuts, true)) {
            $errors[] = "Le statut est obligatoire.";
        }
        if ($data['calories_consommees'] !== '' && $data['calories_consommees'] !== null) {
            if (!ctype_digit((string)$data['calories_consommees']) || (int)$data['calories_consommees'] < 1400) {
                $errors[] = "Les calories doivent être un nombre positif (minimum 1400).";
            }
        }
        if (!empty($data['heure_prevue']) && !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $data['heure_prevue'])) {
            $errors[] = "Format d'heure prévue invalide (HH:MM).";
        }
        if (!empty($data['heure_reelle']) && !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $data['heure_reelle'])) {
            $errors[] = "Format d'heure réelle invalide (HH:MM).";
        }
        if (!empty($data['heure_prevue']) && !empty($data['heure_reelle'])) {
            if ($data['heure_reelle'] < $data['heure_prevue']) {
                $errors[] = "L'heure réelle ne peut pas être antérieure à l'heure prévue.";
            }
        }
        if (strlen($data['notes']) > 1000) {
            $errors[] = "Les notes ne peuvent pas dépasser 1000 caractères.";
        }
        return $errors;
    }

    private function validatePlanData(array $planData, string $action, int $id): array
    {
        $errors = [];
        $nom = $planData['nom'];
        $objectif = $planData['objectif'];
        $utilisateurId = $planData['utilisateur_id'] ?? null;
        $duree = $planData['duree'];
        $preference = $planData['preference'];
        $allergies = $planData['allergies'];

        if (empty($nom) || strlen($nom) < 3 || strlen($nom) > 200) {
            $errors[] = 'Le nom du plan doit contenir entre 3 et 200 caractères.';
        }
        if (empty($objectif)) {
            $errors[] = 'Veuillez choisir un objectif.';
        }
        // Require authenticated user: use session user id when available
        if (session_status() === PHP_SESSION_NONE) session_start();
        $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
        if ($currentUserId) {
            $utilisateurId = (int)$currentUserId;
        } else {
            $errors[] = 'Vous devez être connecté pour créer ou modifier un plan.';
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


        if (!empty($duree)) {
            if (!ctype_digit($duree)) {
                $errors[] = 'La durée doit être un nombre valide.';
            } elseif ((int)$duree < 7) {
                $errors[] = 'La durée doit être au minimum de 7 jours.';
            }
        }

        // utilisateurId will be taken from session; no additional uniqueness checks here

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
        $repasList = $this->repasList;
        $message = $this->message;
        $messageType = $this->messageType;

        // ── Logique spécifique à plan-adapte ──
        $currentPlan = null;
        $repasForFront = [];

        if ($slug === 'plan-adapte') {
            $planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            // If no id provided, try to use the first plan of the current user (or first available)
            if ($planId <= 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                if ($currentUserId && !empty($plans)) {
                    $planId = (int)$plans[0]['id'];
                } elseif (!empty($plans)) {
                    $planId = (int)$plans[0]['id'];
                }
            }

            if ($planId > 0) {
                // Charger un plan existant par son ID
                $currentPlan = $this->model->find($planId);
                if ($currentPlan) {
                    // If user is logged in, ensure ownership
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $currentUserId = $_SESSION['utilisateur']['id'] ?? null;
                    if ($currentUserId && (int)$currentPlan['utilisateur_id'] !== (int)$currentUserId) {
                        $currentPlan = null; // hide plan that doesn't belong to user
                    } else {
                        $rows = $this->repasModel->getByPlanId($planId);
                        // Group by date
                        $grouped = [];
                        foreach ($rows as $r) {
                            $date = $r['date'] ?? 'Jour';
                            if (!isset($grouped[$date])) {
                                $grouped[$date] = ['date' => $date, 'meals' => [], 'totalCalories' => 0];
                            }
                            $meal = [
                                'id' => $r['id'] ?? null,
                                'nom' => $r['nom_recette'] ?? '',
                                'calories' => $r['calories_consommees'] ?? $r['calories'] ?? 0,
                                'description' => $r['notes'] ?? '',
                                'type' => $r['type_repas'] ?? '',
                                'statut' => $r['statut'] ?? 'prevu'
                            ];
                            $grouped[$date]['meals'][] = $meal;
                            $grouped[$date]['totalCalories'] += (int)$meal['calories'];
                        }
                        $repasForFront = array_values($grouped);
                    }
                }
            }
        } else {
            // Pour les autres pages (backoffice), garder le comportement existant
            $firstPlanId = !empty($plans) ? $plans[0]['id'] : 0;
            $repasForFront = $firstPlanId > 0 ? $this->repasModel->getByPlanId($firstPlanId) : [];
        }

        // ── Logique spécifique à plan-nutritionnel ──
        if ($slug === 'plan-nutritionnel') {
            $maxUserId = 0;
            foreach ($plans as $p) {
                $uid = (int)$p['utilisateur_id'];
                if ($uid > $maxUserId) $maxUserId = $uid;
            }
            $nextUserId = $maxUserId + 1;
        }

        include __DIR__ . '/../VIEW/' . $page->getViewFile() . '.php';
    }
}
