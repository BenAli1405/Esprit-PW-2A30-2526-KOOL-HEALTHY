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

    // ========== FONCTIONS DE VALIDATION ==========
    
    /**
     * Valide le nom d'un ingrédient
     */
    private static function validateName($nom) {
        if (empty($nom) || trim($nom) === '') {
            return ['valid' => false, 'message' => 'Le nom de l\'ingredient est requis'];
        }
        if (strlen($nom) < 2) {
            return ['valid' => false, 'message' => 'Le nom doit contenir au moins 2 caracteres'];
        }
        if (strlen($nom) > 100) {
            return ['valid' => false, 'message' => 'Le nom ne peut pas depasser 100 caracteres'];
        }
        // Validation des caracteres (lettres, chiffres, espaces, tirets, apostrophes)
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäæçéèêëïîôöœùûüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜ]+$/u', $nom)) {
            return ['valid' => false, 'message' => 'Le nom contient des caracteres non autorises'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide le format des calories
     */
    private static function validateCalories($calories) {
        if (empty($calories)) {
            return ['valid' => true, 'message' => '']; // Optionnel
        }
        // Format: "120kcal/100g" ou "52kcal"
        if (!preg_match('/^(\d+(?:\.\d+)?)(kcal(?:\/\d+g)?)?$/i', trim($calories))) {
            return ['valid' => false, 'message' => 'Format calories invalide (ex: 120kcal/100g)'];
        }
        return ['valid' => true, 'message' => ''];
    }

    // ========== METHODES PRINCIPALES ==========
    
    // Get all ingredients
    public static function getAllIngredients() {
        return self::getInstance()->getAll();
    }

    // Get ingredient by ID
    public static function getIngredientById($id) {
        return self::getInstance()->getById($id);
    }

    // Create new ingredient avec validation
    public static function createIngredient($nom, $calories = null, $ecoScore = 'A') {
        // Validation du nom
        $nameValidation = self::validateName($nom);
        if (!$nameValidation['valid']) {
            return ['success' => false, 'message' => $nameValidation['message']];
        }
        
        // Validation des calories
        if ($calories) {
            $caloriesValidation = self::validateCalories($calories);
            if (!$caloriesValidation['valid']) {
                return ['success' => false, 'message' => $caloriesValidation['message']];
            }
        }
        
        // Validation de l'éco-score
        $validScores = ['A+', 'A', 'B', 'C', 'D', 'E'];
        if (!in_array($ecoScore, $validScores)) {
            return ['success' => false, 'message' => 'Eco-score invalide'];
        }
        
        // Nettoyage
        $nom = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8');
        
        $newId = self::getInstance()->create($nom, $calories, $ecoScore);
        return ['success' => $newId !== false, 'id' => $newId, 'message' => $newId ? 'Ingredient cree avec succes' : 'Erreur lors de la creation'];
    }

    // Update ingredient avec validation
    public static function updateIngredient($id, $nom, $calories = null, $ecoScore = 'A') {
        // Validation du nom
        $nameValidation = self::validateName($nom);
        if (!$nameValidation['valid']) {
            return ['success' => false, 'message' => $nameValidation['message']];
        }
        
        // Validation des calories
        if ($calories) {
            $caloriesValidation = self::validateCalories($calories);
            if (!$caloriesValidation['valid']) {
                return ['success' => false, 'message' => $caloriesValidation['message']];
            }
        }
        
        // Validation de l'éco-score
        $validScores = ['A+', 'A', 'B', 'C', 'D', 'E'];
        if (!in_array($ecoScore, $validScores)) {
            return ['success' => false, 'message' => 'Eco-score invalide'];
        }
        
        // Nettoyage
        $nom = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8');
        
        $success = self::getInstance()->update($id, $nom, $calories, $ecoScore);
        return ['success' => $success, 'message' => $success ? 'Ingredient mis a jour avec succes' : 'Ingredient non trouve'];
    }

    // Delete ingredient
    public static function deleteIngredient($id) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => 'ID d\'ingredient invalide'];
        }
        error_log("deleteIngredient called with ID: {$id}");
        $success = self::getInstance()->delete($id);
        error_log("deleteIngredient result: " . ($success ? 'true' : 'false'));
        return ['success' => $success, 'message' => $success ? 'Ingredient supprime avec succes' : 'Echec de la suppression'];
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