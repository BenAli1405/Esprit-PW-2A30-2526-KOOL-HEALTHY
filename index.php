<?php
session_start();

require_once __DIR__ . '/controllers/EntrainementController.php';
require_once __DIR__ . '/controllers/ExerciceController.php';
require_once __DIR__ . '/controllers/AdminController.php';

$action = $_GET['action'] ?? 'mes_entrainements';
$entrainementController = new EntrainementController();
$exerciceController = new ExerciceController();
$adminController = new AdminController();

switch ($action) {
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
    case 'recommander_ia':
        $entrainementController->recommend();
        break;
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
    case 'admin_regles':
        $adminController->listRegles();
        break;
    case 'admin_creer_regle':
        $adminController->createRegle();
        break;
    case 'admin_modifier_regle':
        $adminController->editRegle();
        break;
    case 'admin_supprimer_regle':
        $adminController->deleteRegle();
        break;
    default:
        http_response_code(404);
        echo '<h1>404 - Page introuvable</h1>';
        break;
}
