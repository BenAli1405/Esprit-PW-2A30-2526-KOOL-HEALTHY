<?php
// ========== INGREDIENT CONTROLLER ==========
require_once __DIR__ . '/../MODEL/Ingredient.php';

class IngredientC {
    private static $ingredient = null;

    private static function getInstance() {
        if (self::$ingredient === null) {
            self::$ingredient = new Ingredient();
        }
        return self::$ingredient;
    }

    // Get all ingredients
    public static function getAllIngredients() {
        return self::getInstance()->getAll();
    }

    // Get ingredient by ID
    public static function getIngredientById($id) {
        return self::getInstance()->getById($id);
    }

    // Create new ingredient
    public static function createIngredient($nom, $calories = null, $ecoScore = 'A') {
        // Validation
        if (empty($nom)) {
            return ['success' => false, 'message' => 'Nom requis'];
        }
        
        $newId = self::getInstance()->create($nom, $calories, $ecoScore);
        return ['success' => $newId !== false, 'id' => $newId, 'message' => $newId ? 'Ingrédient créé' : 'Erreur'];
    }

    // Update ingredient
    public static function updateIngredient($id, $nom, $calories = null, $ecoScore = 'A') {
        if (empty($nom)) {
            return ['success' => false, 'message' => 'Nom requis'];
        }
        
        $success = self::getInstance()->update($id, $nom, $calories, $ecoScore);
        return ['success' => $success, 'message' => $success ? 'Ingrédient mis à jour' : 'Ingrédient non trouvé'];
    }

    // Delete ingredient
    public static function deleteIngredient($id) {
        error_log("deleteIngredient called with ID: {$id}");
        $success = self::getInstance()->delete($id);
        error_log("deleteIngredient result: " . ($success ? 'true' : 'false'));
        return ['success' => $success, 'message' => $success ? 'Ingrédient supprimé' : 'Échec de la suppression'];
    }

    // Get ingredient with usage info
    public static function getIngredientInfo($id) {
        $ingredient = self::getIngredientById($id);
        if ($ingredient) {
            $ingredient['usageCount'] = self::getInstance()->getUsageCount($id);
        }
        return $ingredient;
    }

    // Get all ingredients with usage count
    public static function getAllIngredientsWithUsage() {
        $ingredients = self::getAllIngredients();
        foreach ($ingredients as &$ing) {
            $ing['usageCount'] = self::getInstance()->getUsageCount($ing['id']);
        }
        return $ingredients;
    }

    // Get top ingredients by usage
    public static function getTopIngredients($limit = 5) {
        return self::getInstance()->getTop($limit);
    }

    // Search ingredients
    public static function searchIngredients($keyword) {
        return self::getInstance()->search($keyword);
    }
}
?>

