<?php

require_once __DIR__ . '/../models/EntrainementModel.php';
require_once __DIR__ . '/../models/ExerciceModel.php';
require_once __DIR__ . '/../models/RecommandationModel.php';

class AdminController
{
    private $entrainementModel;
    private $exerciceModel;
    private $recommendationModel;
    private $errors = [];

    public function __construct()
    {
        $this->entrainementModel = new \EntrainementModel();
        $this->exerciceModel = new \ExerciceModel();
        $this->recommendationModel = new \RecommandationModel();
    }

    public function listEntrainements()
    {
        $entrainements = $this->entrainementModel->getAllWithUser();
        $layout = 'back';
        $action = 'admin_entrainements';
        $pageTitle = 'Kool Healthy | Admin - Entraînements';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/entrainements/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function createEntrainement()
    {
        $data = $this->postData();
        $users = $this->entrainementModel->getAllUsers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validate($data)) {
                $this->entrainementModel->create($data);
                header('Location: index.php?action=admin_entrainements');
                exit;
            }
        }

        $editing = false;
        $layout = 'back';
        $action = 'admin_creer_entrainement';
        $pageTitle = 'Kool Healthy | Admin - Ajouter une séance';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/entrainements/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function editEntrainement()
    {
        $id = (int)($_GET['id'] ?? 0);
        $entrainement = $this->entrainementModel->getById($id);
        $users = $this->entrainementModel->getAllUsers();

        if (!$entrainement) {
            header('Location: index.php?action=admin_entrainements');
            exit;
        }

        $data = $entrainement;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postData();
            if ($this->validate($data)) {
                $this->entrainementModel->update($id, $data);
                header('Location: index.php?action=admin_entrainements');
                exit;
            }
        }

        $editing = true;
        $layout = 'back';
        $action = 'admin_modifier_entrainement';
        $pageTitle = 'Kool Healthy | Admin - Modifier une séance';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/entrainements/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function deleteEntrainement()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->entrainementModel->delete($id);
        }
        header('Location: index.php?action=admin_entrainements');
        exit;
    }

    public function listExercices()
    {
        $exercices = $this->exerciceModel->getAllWithSession();
        $layout = 'back';
        $action = 'admin_exercices';
        $pageTitle = 'Kool Healthy | Admin - Exercices';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/exercices/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function createExercice()
    {
        $data = $this->postExerciceData();
        $entrainements = $this->entrainementModel->getAllWithUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validateExercice($data)) {
                $this->exerciceModel->create($data);
                header('Location: index.php?action=admin_exercices');
                exit;
            }
        }

        $editing = false;
        $layout = 'back';
        $action = 'admin_creer_exercice';
        $pageTitle = 'Kool Healthy | Admin - Ajouter un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function editExercice()
    {
        $id = (int)($_GET['id'] ?? 0);
        $exercice = $this->exerciceModel->getById($id);
        $entrainements = $this->entrainementModel->getAllWithUser();

        if (!$exercice) {
            header('Location: index.php?action=admin_exercices');
            exit;
        }

        $data = $exercice;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postExerciceData();
            if ($this->validateExercice($data)) {
                $this->exerciceModel->update($id, $data);
                header('Location: index.php?action=admin_exercices');
                exit;
            }
        }

        $editing = true;
        $layout = 'back';
        $action = 'admin_modifier_exercice';
        $pageTitle = 'Kool Healthy | Admin - Modifier un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function deleteExercice()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->exerciceModel->delete($id);
        }
        header('Location: index.php?action=admin_exercices');
        exit;
    }

    public function listRegles()
    {
        $regles = $this->recommendationModel->getAll();
        $layout = 'back';
        $action = 'admin_regles';
        $pageTitle = 'Kool Healthy | Admin - Règles IA';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/regles/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function createRegle()
    {
        $data = $this->postRegleData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validateRegle($data)) {
                $this->recommendationModel->create($data);
                header('Location: index.php?action=admin_regles');
                exit;
            }
        }

        $editing = false;
        $layout = 'back';
        $action = 'admin_creer_regle';
        $pageTitle = 'Kool Healthy | Admin - Ajouter une règle';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/regles/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function editRegle()
    {
        $id = (int)($_GET['id'] ?? 0);
        $regle = $this->recommendationModel->getById($id);

        if (!$regle) {
            header('Location: index.php?action=admin_regles');
            exit;
        }

        $data = $regle;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postRegleData();
            if ($this->validateRegle($data)) {
                $this->recommendationModel->update($id, $data);
                header('Location: index.php?action=admin_regles');
                exit;
            }
        }

        $editing = true;
        $layout = 'back';
        $action = 'admin_modifier_regle';
        $pageTitle = 'Kool Healthy | Admin - Modifier une règle';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/back/regles/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function deleteRegle()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->recommendationModel->delete($id);
        }
        header('Location: index.php?action=admin_regles');
        exit;
    }

    private function postData(): array
    {
        return [
            'id_utilisateur' => trim($_POST['id_utilisateur'] ?? ''),
            'date' => trim($_POST['date'] ?? ''),
            'duree_minutes' => trim($_POST['duree_minutes'] ?? ''),
            'type_sport' => trim($_POST['type_sport'] ?? ''),
            'calories_brulees' => trim($_POST['calories_brulees'] ?? ''),
            'commentaire' => trim($_POST['commentaire'] ?? ''),
        ];
    }

    private function postExerciceData(): array
    {
        return [
            'id_entrainement' => trim($_POST['id_entrainement'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'series' => trim($_POST['series'] ?? ''),
            'repetitions' => trim($_POST['repetitions'] ?? ''),
            'repos_secondes' => trim($_POST['repos_secondes'] ?? ''),
            'ordre' => trim($_POST['ordre'] ?? ''),
        ];
    }

    private function validateExercice(array $data): bool
    {
        $this->errors = [];

        if ($data['id_entrainement'] === '' || !ctype_digit($data['id_entrainement'])) {
            $this->errors[] = 'Le choix de la séance est requis.';
        }

        if ($data['nom'] === '') {
            $this->errors[] = 'Le nom de l\'exercice est requis.';
        }

        if ($data['series'] === '' || !ctype_digit($data['series']) || (int)$data['series'] <= 0) {
            $this->errors[] = 'Le nombre de séries doit être un entier positif.';
        }

        if ($data['repetitions'] === '' || !ctype_digit($data['repetitions']) || (int)$data['repetitions'] <= 0) {
            $this->errors[] = 'Le nombre de répétitions doit être un entier positif.';
        }

        if ($data['repos_secondes'] === '' || !ctype_digit($data['repos_secondes']) || (int)$data['repos_secondes'] < 0) {
            $this->errors[] = 'Le repos doit être un nombre de secondes valide.';
        }

        if ($data['ordre'] === '' || !ctype_digit($data['ordre']) || (int)$data['ordre'] <= 0) {
            $this->errors[] = 'L\'ordre de l\'exercice doit être un entier positif.';
        }

        return empty($this->errors);
    }

    private function validate(array $data): bool
    {
        $this->errors = [];

        if ($data['id_utilisateur'] === '' || !ctype_digit($data['id_utilisateur'])) {
            $this->errors[] = 'Le choix de l\'utilisateur est requis.';
        }

        if ($data['date'] === '' || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $data['date'])) {
            $this->errors[] = 'La date est invalide.';
        }

        if ($data['duree_minutes'] === '' || !ctype_digit($data['duree_minutes']) || (int)$data['duree_minutes'] <= 0) {
            $this->errors[] = 'La durée doit être un entier positif.';
        }

        if ($data['type_sport'] === '') {
            $this->errors[] = 'Le type de sport est requis.';
        }

        if ($data['calories_brulees'] === '' || !ctype_digit($data['calories_brulees']) || (int)$data['calories_brulees'] < 0) {
            $this->errors[] = 'Les calories brûlées doivent être un nombre entier.';
        }

        return empty($this->errors);
    }

    private function postRegleData(): array
    {
        return [
            'type_repas' => trim($_POST['type_repas'] ?? ''),
            'exercice_suggere' => trim($_POST['exercice_suggere'] ?? ''),
            'series' => trim($_POST['series'] ?? ''),
            'repetitions' => trim($_POST['repetitions'] ?? ''),
        ];
    }

    private function validateRegle(array $data): bool
    {
        $this->errors = [];

        if ($data['type_repas'] === '') {
            $this->errors[] = 'Le type de repas est requis.';
        }

        if ($data['exercice_suggere'] === '') {
            $this->errors[] = 'Le nom d\'exercice suggéré est requis.';
        }

        if ($data['series'] === '' || !ctype_digit($data['series']) || (int)$data['series'] <= 0) {
            $this->errors[] = 'Le nombre de séries doit être un entier positif.';
        }

        if ($data['repetitions'] === '' || !ctype_digit($data['repetitions']) || (int)$data['repetitions'] <= 0) {
            $this->errors[] = 'Le nombre de répétitions doit être un entier positif.';
        }

        return empty($this->errors);
    }
}
