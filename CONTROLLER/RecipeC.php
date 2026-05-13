<?php
// ========== RECIPE CONTROLLER ==========
require_once __DIR__ . '/../MODEL/Recipe.php';
require_once __DIR__ . '/../MODEL/Ingredient.php';
require_once __DIR__ . '/../DATABASE/Database.php';

class RecipeC {
    private static $recipe = null;
    private static $db     = null;

    private static function getRecipeInstance() {
        if (self::$recipe === null) self::$recipe = new Recipe();
        return self::$recipe;
    }

    private static function getDB() {
        if (self::$db === null) self::$db = new Database();
        return self::$db;
    }

    // ========== VALIDATION ==========

    private static function validateTitle($titre) {
        if (empty($titre) || trim($titre) === '')
            return ['valid' => false, 'message' => 'Le titre est requis'];
        if (strlen($titre) < 3)
            return ['valid' => false, 'message' => 'Le titre doit contenir au moins 3 caracteres'];
        if (strlen($titre) > 100)
            return ['valid' => false, 'message' => 'Le titre ne peut pas depasser 100 caracteres'];
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäæçéèêëïîôöœùûüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜ]+$/u', $titre))
            return ['valid' => false, 'message' => 'Le titre contient des caracteres non autorises'];
        return ['valid' => true, 'message' => ''];
    }

    private static function validateInstructions($instruction) {
        if (empty($instruction) || trim($instruction) === '')
            return ['valid' => false, 'message' => 'Les instructions sont requises'];
        if (strlen($instruction) < 10)
            return ['valid' => false, 'message' => 'Les instructions doivent contenir au moins 10 caracteres'];
        if (strlen($instruction) > 5000)
            return ['valid' => false, 'message' => 'Les instructions ne peuvent pas depasser 5000 caracteres'];
        return ['valid' => true, 'message' => ''];
    }

    private static function validateTime($temp) {
        if (!is_numeric($temp))
            return ['valid' => false, 'message' => 'Le temps doit etre un nombre'];
        $time = intval($temp);
        if ($time < 0)   return ['valid' => false, 'message' => 'Le temps ne peut pas etre negatif'];
        if ($time > 999) return ['valid' => false, 'message' => 'Le temps ne peut pas depasser 999 minutes'];
        return ['valid' => true, 'message' => ''];
    }

    private static function validateDifficulty($difficulte) {
        if (!in_array($difficulte, ['Facile', 'Moyen', 'Difficile']))
            return ['valid' => false, 'message' => 'Difficulte invalide'];
        return ['valid' => true, 'message' => ''];
    }

    private static function validateEcoScore($ecoScore) {
        if (!in_array($ecoScore, ['A+', 'A', 'B', 'C', 'D', 'E']))
            return ['valid' => false, 'message' => 'Eco-score invalide'];
        return ['valid' => true, 'message' => ''];
    }

    private static function validateIngredients($ingredients) {
        if (!is_array($ingredients) || empty($ingredients))
            return ['valid' => false, 'message' => 'Au moins un ingredient est requis'];
        foreach ($ingredients as $index => $ing) {
            if (!isset($ing['idIng']) || !is_numeric($ing['idIng']) || $ing['idIng'] <= 0)
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': selection invalide'];
            if (!isset($ing['qty']) || !is_numeric($ing['qty']) || $ing['qty'] <= 0)
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': quantite invalide'];
            if ($ing['qty'] > 9999)
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': quantite trop elevee'];
            if (isset($ing['unite']) && strlen($ing['unite']) > 20)
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': unite trop longue'];
        }
        return ['valid' => true, 'message' => ''];
    }

    // ========== PUBLIC METHODS ==========

    public static function getAllRecipes() {
        return self::getRecipeInstance()->getAll();
    }

    public static function getRecipeById($id) {
        return self::getRecipeInstance()->getWithIngredients($id);
    }

    /**
     * Return nutrition + labels + goal-match for a single recipe.
     * Used by the optional ?action=getRecipeNutrition&id=X endpoint.
     */
    public static function getRecipeNutrition($id) {
        $recipe = self::getRecipeInstance()->getWithIngredients($id);
        if (!$recipe) return ['success' => false, 'message' => 'Recette non trouvee'];

        $nutrition  = $recipe['nutrition']  ?? [];
        $labels     = $recipe['labels']     ?? [];
        $goalMatch  = self::getRecipeInstance()->computeGoalMatch($nutrition);

        return [
            'success'   => true,
            'id'        => $id,
            'titre'     => $recipe['titre'],
            'nutrition' => $nutrition,
            'labels'    => $labels,
            'goals'     => $goalMatch,
        ];
    }

    public static function createRecipe($utilisateurId, $titre, $instruction, $temp,
                                        $difficulte, $ecoScore, $ingredients) {
        foreach ([
            self::validateTitle($titre),
            self::validateInstructions($instruction),
            self::validateTime($temp),
            self::validateDifficulty($difficulte),
            self::validateEcoScore($ecoScore),
            self::validateIngredients($ingredients),
        ] as $v) {
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
        }

        $newId = self::getRecipeInstance()->create($titre, $instruction, $temp,
                                                   $difficulte, $ecoScore, $ingredients);
        return [
            'success' => $newId !== false,
            'id'      => $newId,
            'message' => $newId ? 'Recette creee avec succes' : 'Erreur lors de la creation',
        ];
    }

    public static function updateRecipe($id, $titre, $instruction, $temp,
                                        $difficulte, $ecoScore, $ingredients) {
        foreach ([
            self::validateTitle($titre),
            self::validateInstructions($instruction),
            self::validateTime($temp),
            self::validateDifficulty($difficulte),
            self::validateEcoScore($ecoScore),
            self::validateIngredients($ingredients),
        ] as $v) {
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
        }

        $success = self::getRecipeInstance()->update($id, $titre, $instruction, $temp,
                                                     $difficulte, $ecoScore, $ingredients);
        return [
            'success' => $success,
            'message' => $success ? 'Recette mise a jour avec succes' : 'Recette non trouvee',
        ];
    }

    public static function deleteRecipe($id) {
        if (!is_numeric($id) || $id <= 0)
            return ['success' => false, 'message' => 'ID de recette invalide'];
        $success = self::getRecipeInstance()->delete($id);
        return [
            'success' => $success,
            'message' => $success ? 'Recette supprimee avec succes' : 'Echec de la suppression',
        ];
    }

    public static function addReview($recipeId, $utilisateur, $note, $commentaire) {
        if (!is_numeric($note) || $note < 1 || $note > 5)
            return ['success' => false, 'message' => 'La note doit etre comprise entre 1 et 5'];
        if (empty($utilisateur) || trim($utilisateur) === '') $utilisateur = 'Anonyme';
        if (strlen($utilisateur) > 100) $utilisateur = substr($utilisateur, 0, 100);
        if (empty($commentaire) || trim($commentaire) === '')
            return ['success' => false, 'message' => 'Le commentaire est requis'];
        if (strlen($commentaire) > 1000)
            return ['success' => false, 'message' => 'Le commentaire ne peut pas depasser 1000 caracteres'];

        $utilisateur = htmlspecialchars(trim($utilisateur), ENT_QUOTES, 'UTF-8');
        $commentaire = htmlspecialchars(trim($commentaire), ENT_QUOTES, 'UTF-8');

        $success = self::getRecipeInstance()->addReview($recipeId, $utilisateur, $note, $commentaire);
        return [
            'success' => $success,
            'message' => $success ? 'Avis ajoute avec succes' : 'Erreur lors de l\'ajout de l\'avis',
        ];
    }

    public static function deleteReview($recipeId, $reviewId) {
        if (!is_numeric($recipeId) || $recipeId <= 0)
            return ['success' => false, 'message' => 'ID de recette invalide'];
        if (!is_numeric($reviewId) || $reviewId <= 0)
            return ['success' => false, 'message' => 'ID d\'avis invalide'];
        $success = self::getRecipeInstance()->deleteReview($recipeId, $reviewId);
        return [
            'success' => $success,
            'message' => $success ? 'Avis supprime avec succes' : 'Echec de la suppression',
        ];
    }

    public static function getDashboardStats() {
        $db = self::getDB();
        return [
            'recettes'    => $db->fetchOne("SELECT COUNT(*) as c FROM recettes WHERE est_actif = TRUE")['c'],
            'ingredients' => $db->fetchOne("SELECT COUNT(*) as c FROM ingredients")['c'],
            'avis'        => $db->fetchOne("SELECT COUNT(*) as c FROM avis")['c'],
        ];
    }

    public static function getTopRecipes($limit = 5) {
        $recipes = self::getAllRecipes();
        $withRating = [];
        foreach ($recipes as $recipe) {
            $withRating[] = [
                'id'      => $recipe['id'],
                'titre'   => $recipe['titre'],
                'avgNote' => $recipe['note_moyenne'] ?? 0,
                'avis'    => $recipe['nombre_avis']  ?? 0,
            ];
        }
        usort($withRating, fn($a, $b) => ($b['avgNote'] ?? 0) <=> ($a['avgNote'] ?? 0));
        return array_slice($withRating, 0, $limit);
    }
}
?>