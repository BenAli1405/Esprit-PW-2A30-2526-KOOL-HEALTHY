<?php

require_once __DIR__ . '/../models/ExerciceModel.php';
require_once __DIR__ . '/../models/EntrainementModel.php';

class ExerciceController
{
    private $model;
    private $entrainementModel;
    private $errors = [];

    public function __construct()
    {
        $this->model = new \ExerciceModel();
        $this->entrainementModel = new \EntrainementModel();
    }

    public function index()
    {
        $idEntrainement = (int)($_GET['id'] ?? 0);
        $entrainement = $this->entrainementModel->getById($idEntrainement);

        if (!$entrainement) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $exercices = $this->model->getAllByEntrainement($idEntrainement);
        $layout = 'front';
        $action = 'voir_exercices';
        $pageTitle = 'Kool Healthy | Exercices de la séance';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function create()
    {
        $idEntrainement = (int)($_GET['id'] ?? 0);
        $entrainement = $this->entrainementModel->getById($idEntrainement);

        if (!$entrainement) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $data = $this->postData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validate($data)) {
                $data['id_entrainement'] = $idEntrainement;
                $this->model->create($data);
                header('Location: index.php?action=voir_exercices&id=' . $idEntrainement);
                exit;
            }
        }

        $editing = false;
        $layout = 'front';
        $action = 'ajouter_exercice';
        $pageTitle = 'Kool Healthy | Ajouter un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $exercice = $this->model->getById($id);

        if (!$exercice) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $entrainement = $this->entrainementModel->getById($exercice['id_entrainement']);
        $data = $exercice;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postData();
            if ($this->validate($data)) {
                $this->model->update($id, $data);
                header('Location: index.php?action=voir_exercices&id=' . $exercice['id_entrainement']);
                exit;
            }
        }

        $editing = true;
        $layout = 'front';
        $action = 'modifier_exercice';
        $pageTitle = 'Kool Healthy | Modifier un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        $exercice = $this->model->getById($id);

        if ($exercice) {
            $this->model->delete($id);
            header('Location: index.php?action=voir_exercices&id=' . $exercice['id_entrainement']);
            exit;
        }

        header('Location: index.php?action=mes_entrainements');
        exit;
    }

    private function postData(): array
    {
        return [
            'nom' => trim($_POST['nom'] ?? ''),
            'series' => trim($_POST['series'] ?? ''),
            'repetitions' => trim($_POST['repetitions'] ?? ''),
            'repos_secondes' => trim($_POST['repos_secondes'] ?? ''),
            'ordre' => trim($_POST['ordre'] ?? ''),
        ];
    }

    private function validate(array $data): bool
    {
        $this->errors = [];

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
}
