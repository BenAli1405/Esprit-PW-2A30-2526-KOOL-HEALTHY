<?php
// ========== KOOL HEALTHY - INDEX.PHP (MAIN ROUTER) ==========
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'config.php';
require_once CONTROLLER_PATH . 'RecipeC.php';
require_once CONTROLLER_PATH . 'IngredientC.php';
require_once CONTROLLER_PATH . 'UserC.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'frontoffice';
$view   = isset($_GET['view'])   ? $_GET['view']   : 'frontoffice';
$baseUrl = BASE_URL;

// ========== GET REQUESTS (AJAX) ==========
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        switch ($_GET['action']) {

            // ----- Recipes -----
            case 'getAllRecipes':
                echo json_encode(RecipeC::getAllRecipes());
                exit;

            case 'getRecipeById':
                $id = isset($_GET['id']) ? intval($_GET['id']) : null;
                echo json_encode(RecipeC::getRecipeById($id));
                exit;

            case 'getRecipeNutrition':
                $id = isset($_GET['id']) ? intval($_GET['id']) : null;
                echo json_encode(RecipeC::getRecipeNutrition($id));
                exit;

            case 'getDashboardStats':
                echo json_encode(RecipeC::getDashboardStats());
                exit;

            // ----- Ingredients -----
            case 'getAllIngredients':
                echo json_encode(IngredientC::getAllIngredients());
                exit;

            case 'getIngredientById':
                $id = isset($_GET['id']) ? intval($_GET['id']) : null;
                echo json_encode(IngredientC::getIngredientById($id));
                exit;

            // ----- Users -----
            case 'getAllUsers':
                echo json_encode(UserC::getAllUsers());
                exit;

            case 'getUserById':
                $id = isset($_GET['id']) ? intval($_GET['id']) : null;
                echo json_encode(UserC::getUserById($id));
                exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ========== POST REQUESTS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    try {
        switch ($action) {

            // ----- Recipe CRUD -----
            case 'createRecipe':
                echo json_encode(RecipeC::createRecipe(
                    $_POST['utilisateurId'] ?? 1,
                    $_POST['titre']         ?? '',
                    $_POST['instruction']   ?? '',
                    $_POST['temp']          ?? 0,
                    $_POST['difficulte']    ?? 'Facile',
                    $_POST['ecoScore']      ?? 'A',
                    json_decode($_POST['ingredients'] ?? '[]', true)
                ));
                exit;

            case 'updateRecipe':
                echo json_encode(RecipeC::updateRecipe(
                    $_POST['id']          ?? 0,
                    $_POST['titre']       ?? '',
                    $_POST['instruction'] ?? '',
                    $_POST['temp']        ?? 0,
                    $_POST['difficulte']  ?? 'Facile',
                    $_POST['ecoScore']    ?? 'A',
                    json_decode($_POST['ingredients'] ?? '[]', true)
                ));
                exit;

            case 'deleteRecipe':
                echo json_encode(RecipeC::deleteRecipe($_POST['id'] ?? 0));
                exit;

            case 'addReview':
                echo json_encode(RecipeC::addReview(
                    $_POST['recipeId']    ?? 0,
                    $_POST['utilisateur'] ?? 'Anonyme',
                    $_POST['note']        ?? 0,
                    $_POST['commentaire'] ?? ''
                ));
                exit;

            case 'updateReview':
                echo json_encode(RecipeC::updateReview(
                    $_POST['id']          ?? 0,
                    $_POST['note']        ?? 0,
                    $_POST['commentaire'] ?? ''
                ));
                exit;

            case 'deleteReview':
                echo json_encode(RecipeC::deleteReview(
                    $_POST['recipeId'] ?? 0,
                    $_POST['id']       ?? 0
                ));
                exit;

            // ----- Ingredient CRUD (now includes nutritional fields) -----
            case 'createIngredient':
                echo json_encode(IngredientC::createIngredient(
                    $_POST['nom']       ?? '',
                    $_POST['calories']  ?? null,
                    $_POST['ecoScore']  ?? 'A',
                    $_POST['proteines'] ?? 0,
                    $_POST['glucides']  ?? 0,
                    $_POST['lipides']   ?? 0,
                    $_POST['fibres']    ?? 0,
                    $_POST['sel']       ?? 0
                ));
                exit;

            case 'updateIngredient':
                echo json_encode(IngredientC::updateIngredient(
                    $_POST['id']        ?? 0,
                    $_POST['nom']       ?? '',
                    $_POST['calories']  ?? null,
                    $_POST['ecoScore']  ?? 'A',
                    $_POST['proteines'] ?? 0,
                    $_POST['glucides']  ?? 0,
                    $_POST['lipides']   ?? 0,
                    $_POST['fibres']    ?? 0,
                    $_POST['sel']       ?? 0
                ));
                exit;

            case 'deleteIngredient':
                echo json_encode(IngredientC::deleteIngredient($_POST['id'] ?? 0));
                exit;

            // ----- User CRUD -----
            case 'createUser':
                echo json_encode(UserC::createUser(
                    $_POST['nom']   ?? '',
                    $_POST['email'] ?? ''
                ));
                exit;

            case 'updateUser':
                echo json_encode(UserC::updateUser(
                    $_POST['id']     ?? 0,
                    $_POST['nom']    ?? '',
                    $_POST['email']  ?? '',
                    $_POST['statut'] ?? 'actif'
                ));
                exit;

            case 'toggleBlockUser':
                echo json_encode(UserC::toggleBlockUser(
                    $_POST['id']    ?? 0,
                    isset($_POST['block']) ? (bool)$_POST['block'] : true
                ));
                exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ========== VIEWS ==========
switch ($view) {
    case 'backoffice':
        require VIEW_PATH . 'backoffice.html';
        break;
    case 'frontoffice':
    default:
        require VIEW_PATH . 'frontoffice.php';
        break;
}
?>