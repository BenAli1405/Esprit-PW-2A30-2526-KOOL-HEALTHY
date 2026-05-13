<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EntrainementController.php';
require_once __DIR__ . '/../controllers/ExerciceController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

$action = $_GET['action'] ?? 'mes_entrainements';

$entrainementController = new EntrainementController();
$exerciceController     = new ExerciceController();
$adminController        = new AdminController();

switch ($action) {
    // ── Front utilisateur ──
    case 'mes_entrainements':
        $entrainementController->index();
        break;
    case 'ajouter_entrainement':
        $entrainementController->create();
        break;
    case 'modifier_entrainement':
        $entrainementController->edit();
        break;
    case 'supprimer_entrainement':
        $entrainementController->delete();
        break;
    case 'statistiques':
    case 'statistiques_entrainements':
        $entrainementController->statistiques();
        break;
    case 'voir_exercices':
        $exerciceController->index();
        break;
    case 'ajouter_exercice':
        $exerciceController->create();
        break;
    case 'modifier_exercice':
        $exerciceController->edit();
        break;
    case 'supprimer_exercice':
        $exerciceController->delete();
        break;
    case 'recommander':
    case 'recommander_exercices':
        $exerciceController->recommanderKnn();
        break;
    case 'progression':
    case 'progression_exercice':
        $exerciceController->progression();
        break;

    // ── Back-office admin ──
    case 'admin_entrainements':
        $adminController->listEntrainements();
        break;
    case 'admin_creer_entrainement':
        $adminController->createEntrainement();
        break;
    case 'admin_modifier_entrainement':
        $adminController->editEntrainement();
        break;
    case 'admin_supprimer_entrainement':
        $adminController->deleteEntrainement();
        break;
    case 'admin_exercices':
        $adminController->listExercices();
        break;
    case 'admin_creer_exercice':
        $adminController->createExercice();
        break;
    case 'admin_modifier_exercice':
        $adminController->editExercice();
        break;
    case 'admin_supprimer_exercice':
        $adminController->deleteExercice();
        break;
    case 'admin_reference_list':
        $adminController->listReference();
        break;
    case 'admin_reference_create':
        $adminController->createReference();
        break;
    case 'admin_reference_edit':
        $adminController->editReference();
        break;
    case 'admin_reference_delete':
        $adminController->deleteReference();
        break;

    default:
        $entrainementController->index();
        break;
}
