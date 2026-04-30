<?php

require_once __DIR__ . '/../models/EntrainementModel.php';
require_once __DIR__ . '/../models/RecommandationModel.php';

class EntrainementController
{
    private $model;
    private $recommendationModel;
    private $errors = [];

    public function __construct()
    {
        $this->model = new \EntrainementModel();
        $this->recommendationModel = new \RecommandationModel();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        
        // Récupérer le filtre par type de sport depuis l'URL
        $typeSportFilter = $_GET['type_sport'] ?? null;
        
        // Récupérer le paramètre de recherche
        $search = $_GET['search'] ?? null;
        
        // Récupérer les séances (avec filtres optionnels)
        $entrainements = $this->model->getAllByUser($userId, $typeSportFilter, $search);
        
        // Ajouter le compteur d'exercices pour chaque séance
        foreach ($entrainements as &$entrainement) {
            $entrainement['nb_exercices'] = $this->model->getExercicesCount($entrainement['id_entrainement']);
        }
        unset($entrainement);
        
        // Récupérer les types de sports distincts pour le filtre
        $sportTypes = $this->model->getDistinctSportTypes();
        
        $layout = 'front';
        $action = 'mes_entrainements';
        $pageTitle = 'Kool Healthy | Entraînement & Exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/entrainements/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Affiche les statistiques de calories par semaine
     */
    public function statistiques()
    {
        $userId = $this->getCurrentUserId();
        
        // Récupérer les données de calories par semaine (4 dernières semaines)
        $caloriesPerWeek = $this->model->getCaloriesPerWeek($userId, 4);
        
        $layout = 'front';
        $action = 'statistiques_entrainements';
        $pageTitle = 'Kool Healthy | Statistiques - Calories par Semaine';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/entrainements/statistiques.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function create()
    {
        $data = $this->postData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validate($data)) {
                $data['id_utilisateur'] = $this->getCurrentUserId();
                $this->model->create($data);
                header('Location: index.php?action=mes_entrainements');
                exit;
            }
        }

        $editing = false;
        $layout = 'front';
        $action = 'ajouter_entrainement';
        $pageTitle = 'Kool Healthy | Ajouter un entraînement';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/entrainements/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $entrainement = $this->model->getById($id);

        if (!$entrainement) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $data = $entrainement;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postData();
            $data['id_utilisateur'] = $entrainement['id_utilisateur'];
            if ($this->validate($data)) {
                $this->model->update($id, $data);
                header('Location: index.php?action=mes_entrainements');
                exit;
            }
        }

        $editing = true;
        $layout = 'front';
        $action = 'modifier_entrainement';
        $pageTitle = 'Kool Healthy | Modifier un entraînement';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/entrainements/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }
        header('Location: index.php?action=mes_entrainements');
        exit;
    }

    public function recommend()
    {
        $suggestions = [];
        $selectedType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedType = trim($_POST['type_repas'] ?? '');
            if ($selectedType === '') {
                $this->errors[] = 'Veuillez choisir un type de repas.';
            } else {
                $suggestions = $this->recommendationModel->findByTypeRepas($selectedType);
                if (empty($suggestions)) {
                    $this->errors[] = 'Aucune recommandation disponible pour ce type de repas.';
                }
            }
        }

        $layout = 'front';
        $action = 'recommander_ia';
        $pageTitle = 'Kool Healthy | Recommander IA';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/entrainements/recommander.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    private function postData(): array
    {
        return [
            'date' => trim($_POST['date'] ?? ''),
            'duree_minutes' => trim($_POST['duree_minutes'] ?? ''),
            'type_sport' => trim($_POST['type_sport'] ?? ''),
            'calories_brulees' => trim($_POST['calories_brulees'] ?? ''),
            'commentaire' => trim($_POST['commentaire'] ?? ''),
        ];
    }

    private function validate(array $data): bool
    {
        $this->errors = [];

        if ($data['date'] === '' || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $data['date'])) {
            $this->errors[] = 'La date est invalide.';
        }

        if ($data['duree_minutes'] === '' || !ctype_digit($data['duree_minutes']) || (int)$data['duree_minutes'] <= 0) {
            $this->errors[] = 'La durée doit être un nombre entier positif.';
        }

        if ($data['type_sport'] === '') {
            $this->errors[] = 'Le type de sport est requis.';
        }

        if ($data['calories_brulees'] === '' || !ctype_digit($data['calories_brulees']) || (int)$data['calories_brulees'] < 0) {
            $this->errors[] = 'Les calories brûlées doivent être un nombre entier.';
        }

        return empty($this->errors);
    }

    private function getCurrentUserId(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
        }
        return (int)$_SESSION['user_id'];
    }
}