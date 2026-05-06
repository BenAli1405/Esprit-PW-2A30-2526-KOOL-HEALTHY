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

    // ========== VALIDATION ==========

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
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäæçéèêëïîôöœùûüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜ]+$/u', $nom)) {
            return ['valid' => false, 'message' => 'Le nom contient des caracteres non autorises'];
        }
        return ['valid' => true, 'message' => ''];
    }

    private static function validateCalories($calories) {
        if (empty($calories)) {
            return ['valid' => true, 'message' => ''];
        }
        if (!preg_match('/^(\d+(?:\.\d+)?)(kcal(?:\/\d+g)?)?$/i', trim($calories))) {
            return ['valid' => false, 'message' => 'Format calories invalide (ex: 120kcal/100g)'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a nutritional value (must be a non-negative number)
     */
    private static function validateNutritionalValue($value, $fieldName) {
        if ($value === '' || $value === null) {
            return ['valid' => true, 'message' => ''];
        }
        if (!is_numeric($value)) {
            return ['valid' => false, 'message' => "$fieldName doit etre un nombre"];
        }
        if ((float)$value < 0) {
            return ['valid' => false, 'message' => "$fieldName ne peut pas etre negatif"];
        }
        if ((float)$value > 999) {
            return ['valid' => false, 'message' => "$fieldName semble trop eleve (max 999g)"];
        }
        return ['valid' => true, 'message' => ''];
    }

    // ========== PUBLIC METHODS ==========

    public static function getAllIngredients() {
        return self::getInstance()->getAll();
    }

    public static function getIngredientById($id) {
        return self::getInstance()->getById($id);
    }

    public static function createIngredient($nom, $calories = null, $ecoScore = 'A',
                                            $proteines = 0, $glucides = 0, $lipides = 0,
                                            $fibres = 0, $sel = 0) {
        // Validate name
        $nameValidation = self::validateName($nom);
        if (!$nameValidation['valid']) {
            return ['success' => false, 'message' => $nameValidation['message']];
        }
        // Validate calories
        if ($calories) {
            $calVal = self::validateCalories($calories);
            if (!$calVal['valid']) return ['success' => false, 'message' => $calVal['message']];
        }
        // Validate eco-score
        if (!in_array($ecoScore, ['A+', 'A', 'B', 'C', 'D', 'E'])) {
            return ['success' => false, 'message' => 'Eco-score invalide'];
        }
        // Validate nutritional values
        foreach ([
            [$proteines, 'Protéines'],
            [$glucides,  'Glucides'],
            [$lipides,   'Lipides'],
            [$fibres,    'Fibres'],
            [$sel,       'Sel'],
        ] as [$val, $label]) {
            $v = self::validateNutritionalValue($val, $label);
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
        }

        $nom   = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8');
        $newId = self::getInstance()->create($nom, $calories, $ecoScore,
                                             $proteines, $glucides, $lipides, $fibres, $sel);
        return [
            'success' => $newId !== false,
            'id'      => $newId,
            'message' => $newId ? 'Ingredient cree avec succes' : 'Erreur lors de la creation',
        ];
    }

    public static function updateIngredient($id, $nom, $calories = null, $ecoScore = 'A',
                                            $proteines = 0, $glucides = 0, $lipides = 0,
                                            $fibres = 0, $sel = 0) {
        $nameValidation = self::validateName($nom);
        if (!$nameValidation['valid']) {
            return ['success' => false, 'message' => $nameValidation['message']];
        }
        if ($calories) {
            $calVal = self::validateCalories($calories);
            if (!$calVal['valid']) return ['success' => false, 'message' => $calVal['message']];
        }
        if (!in_array($ecoScore, ['A+', 'A', 'B', 'C', 'D', 'E'])) {
            return ['success' => false, 'message' => 'Eco-score invalide'];
        }
        foreach ([
            [$proteines, 'Protéines'],
            [$glucides,  'Glucides'],
            [$lipides,   'Lipides'],
            [$fibres,    'Fibres'],
            [$sel,       'Sel'],
        ] as [$val, $label]) {
            $v = self::validateNutritionalValue($val, $label);
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
        }

        $nom     = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8');
        $success = self::getInstance()->update($id, $nom, $calories, $ecoScore,
                                               $proteines, $glucides, $lipides, $fibres, $sel);
        return [
            'success' => $success,
            'message' => $success ? 'Ingredient mis a jour avec succes' : 'Ingredient non trouve',
        ];
    }

    public static function deleteIngredient($id) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => 'ID d\'ingredient invalide'];
        }
        $success = self::getInstance()->delete($id);
        return [
            'success' => $success,
            'message' => $success ? 'Ingredient supprime avec succes' : 'Echec de la suppression',
        ];
    }

    public static function getIngredientInfo($id) {
        $ingredient = self::getIngredientById($id);
        if ($ingredient) {
            $ingredient['usageCount'] = self::getInstance()->getUsageCount($id);
        }
        return $ingredient;
    }

    public static function getAllIngredientsWithUsage() {
        $ingredients = self::getAllIngredients();
        foreach ($ingredients as &$ing) {
            $ing['usageCount'] = self::getInstance()->getUsageCount($ing['id']);
        }
        return $ingredients;
    }

    public static function getTopIngredients($limit = 5) {
        return self::getInstance()->getTop($limit);
    }

    public static function searchIngredients($keyword) {
        return self::getInstance()->search($keyword);
    }
}
?>