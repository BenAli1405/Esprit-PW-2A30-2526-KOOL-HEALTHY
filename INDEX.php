<?php
// ========== KOOL HEALTHY - INDEX.PHP (MAIN ROUTER) ==========
// This is the entry point for the MVC application

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'config.php';
require_once CONTROLLER_PATH . 'RecipeC.php';
require_once CONTROLLER_PATH . 'IngredientC.php';
require_once CONTROLLER_PATH . 'UserC.php';

// Determine the action/view to display
$action = isset($_GET['action']) ? $_GET['action'] : 'frontoffice';
$view = isset($_GET['view']) ? $_GET['view'] : 'frontoffice';

// Set base URL for views
$baseUrl = BASE_URL;

// Handle AJAX requests
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
    
    switch ($_GET['action']) {
        // Recipe Actions
        case 'getAllRecipes':
            echo json_encode(RecipeC::getAllRecipes());
            exit;
        
        case 'getRecipeById':
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            echo json_encode(RecipeC::getRecipeById($id));
            exit;
        
        case 'getDashboardStats':
            echo json_encode(RecipeC::getDashboardStats());
            exit;
        
        // Ingredient Actions
        case 'getAllIngredients':
            echo json_encode(IngredientC::getAllIngredients());
            exit;
        
        case 'getIngredientById':
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            echo json_encode(IngredientC::getIngredientById($id));
            exit;
        
        // User Actions
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

// Handle POST requests (Form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    
    try {
        switch ($action) {
            // Recipe Operations
            case 'createRecipe':
                $result = RecipeC::createRecipe(
                    $_POST['utilisateurId'] ?? 1,
                    $_POST['titre'] ?? '',
                    $_POST['instruction'] ?? '',
                    $_POST['temp'] ?? 0,
                    $_POST['difficulte'] ?? 'Facile',
                    $_POST['ecoScore'] ?? 'A',
                    json_decode($_POST['ingredients'] ?? '[]', true)
                );
                echo json_encode($result);
                exit;
        
        case 'updateRecipe':
            $result = RecipeC::updateRecipe(
                $_POST['id'] ?? 0,
                $_POST['titre'] ?? '',
                $_POST['instruction'] ?? '',
                $_POST['temp'] ?? 0,
                $_POST['difficulte'] ?? 'Facile',
                $_POST['ecoScore'] ?? 'A',
                json_decode($_POST['ingredients'] ?? '[]', true)
            );
            echo json_encode($result);
            exit;
        
        case 'deleteRecipe':
            $result = RecipeC::deleteRecipe($_POST['id'] ?? 0);
            echo json_encode($result);
            exit;
        
        case 'addReview':
            $result = RecipeC::addReview(
                $_POST['recipeId'] ?? 0,
                $_POST['utilisateur'] ?? 'Anonyme',
                $_POST['note'] ?? 0,
                $_POST['commentaire'] ?? ''
            );
            echo json_encode($result);
            exit;
        
        case 'deleteReview':
            $result = RecipeC::deleteReview(
                $_POST['recipeId'] ?? 0,
                $_POST['id'] ?? 0
            );
            echo json_encode($result);
            exit;
        
        // Ingredient Operations
        case 'createIngredient':
            $result = IngredientC::createIngredient(
                $_POST['nom'] ?? '',
                $_POST['calories'] ?? '',
                $_POST['ecoScore'] ?? 'A'
            );
            echo json_encode($result);
            exit;
        
        case 'updateIngredient':
            $result = IngredientC::updateIngredient(
                $_POST['id'] ?? 0,
                $_POST['nom'] ?? '',
                $_POST['calories'] ?? '',
                $_POST['ecoScore'] ?? 'A'
            );
            echo json_encode($result);
            exit;
        
        case 'deleteIngredient':
            $result = IngredientC::deleteIngredient($_POST['id'] ?? 0);
            echo json_encode($result);
            exit;
        
        // User Operations
        case 'createUser':
            $result = UserC::createUser(
                $_POST['nom'] ?? '',
                $_POST['email'] ?? ''
            );
            echo json_encode($result);
            exit;
        
        case 'updateUser':
            $result = UserC::updateUser(
                $_POST['id'] ?? 0,
                $_POST['nom'] ?? '',
                $_POST['email'] ?? '',
                $_POST['statut'] ?? 'actif'
            );
            echo json_encode($result);
            exit;
        
        case 'toggleBlockUser':
            $result = UserC::toggleBlockUser(
                $_POST['id'] ?? 0,
                isset($_POST['block']) ? (bool)$_POST['block'] : true
            );
            echo json_encode($result);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Display the appropriate view
switch ($view) {
    case 'backoffice':
        require VIEW_PATH . 'backoffice.html';
        break;
    
    case 'frontoffice':
    default:
        require VIEW_PATH . 'frontoffice.html';
        break;
}
?>
