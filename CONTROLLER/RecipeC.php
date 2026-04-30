<?php
// ========== RECIPE CONTROLLER ==========
require_once __DIR__ . '/../MODEL/Recipe.php';
require_once __DIR__ . '/../MODEL/Ingredient.php';
require_once __DIR__ . '/../DATABASE/Database.php';

class RecipeC {
    private static $recipe = null;
    private static $db = null;

    private static function getRecipeInstance() {
        if (self::$recipe === null) {
            self::$recipe = new Recipe();
        }
        return self::$recipe;
    }

    private static function getDB() {
        if (self::$db === null) {
            self::$db = new Database();
        }
        return self::$db;
    }

    // ========== FONCTIONS DE VALIDATION ==========
    
    /**
     * Valide le titre d'une recette
     */
    private static function validateTitle($titre) {
        if (empty($titre) || trim($titre) === '') {
            return ['valid' => false, 'message' => 'Le titre est requis'];
        }
        if (strlen($titre) < 3) {
            return ['valid' => false, 'message' => 'Le titre doit contenir au moins 3 caracteres'];
        }
        if (strlen($titre) > 100) {
            return ['valid' => false, 'message' => 'Le titre ne peut pas depasser 100 caracteres'];
        }
        // Validation des caracteres (lettres, chiffres, espaces, tirets, apostrophes)
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäæçéèêëïîôöœùûüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜ]+$/u', $titre)) {
            return ['valid' => false, 'message' => 'Le titre contient des caracteres non autorises'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide les instructions
     */
    private static function validateInstructions($instruction) {
        if (empty($instruction) || trim($instruction) === '') {
            return ['valid' => false, 'message' => 'Les instructions sont requises'];
        }
        if (strlen($instruction) < 10) {
            return ['valid' => false, 'message' => 'Les instructions doivent contenir au moins 10 caracteres'];
        }
        if (strlen($instruction) > 5000) {
            return ['valid' => false, 'message' => 'Les instructions ne peuvent pas depasser 5000 caracteres'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide le temps de préparation
     */
    private static function validateTime($temp) {
        if (!is_numeric($temp)) {
            return ['valid' => false, 'message' => 'Le temps doit etre un nombre'];
        }
        $time = intval($temp);
        if ($time < 0) {
            return ['valid' => false, 'message' => 'Le temps ne peut pas etre negatif'];
        }
        if ($time > 999) {
            return ['valid' => false, 'message' => 'Le temps ne peut pas depasser 999 minutes'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide la difficulté
     */
    private static function validateDifficulty($difficulte) {
        $validDifficulties = ['Facile', 'Moyen', 'Difficile'];
        if (!in_array($difficulte, $validDifficulties)) {
            return ['valid' => false, 'message' => 'Difficulte invalide'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide l'éco-score
     */
    private static function validateEcoScore($ecoScore) {
        $validScores = ['A+', 'A', 'B', 'C', 'D', 'E'];
        if (!in_array($ecoScore, $validScores)) {
            return ['valid' => false, 'message' => 'Eco-score invalide'];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Valide les ingrédients
     */
    private static function validateIngredients($ingredients) {
        if (!is_array($ingredients) || empty($ingredients)) {
            return ['valid' => false, 'message' => 'Au moins un ingredient est requis'];
        }
        
        foreach ($ingredients as $index => $ing) {
            if (!isset($ing['idIng']) || !is_numeric($ing['idIng']) || $ing['idIng'] <= 0) {
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': selection invalide'];
            }
            if (!isset($ing['qty']) || !is_numeric($ing['qty']) || $ing['qty'] <= 0) {
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': quantite invalide'];
            }
            if ($ing['qty'] > 9999) {
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': quantite trop elevee'];
            }
            if (isset($ing['unite']) && strlen($ing['unite']) > 20) {
                return ['valid' => false, 'message' => 'Ingredient ' . ($index + 1) . ': unite trop longue'];
            }
        }
        return ['valid' => true, 'message' => ''];
    }

    // ========== METHODES PRINCIPALES ==========
    
    // Get all recipes
    public static function getAllRecipes() {
        return self::getRecipeInstance()->getAll();
    }

    // Get recipe by ID with ingredients
    public static function getRecipeById($id) {
        return self::getRecipeInstance()->getWithIngredients($id);
    }

    // Create new recipe avec validation
    public static function createRecipe($utilisateurId, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        // Validation du titre
        $titleValidation = self::validateTitle($titre);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'message' => $titleValidation['message']];
        }
        
        // Validation des instructions
        $instructionsValidation = self::validateInstructions($instruction);
        if (!$instructionsValidation['valid']) {
            return ['success' => false, 'message' => $instructionsValidation['message']];
        }
        
        // Validation du temps
        $timeValidation = self::validateTime($temp);
        if (!$timeValidation['valid']) {
            return ['success' => false, 'message' => $timeValidation['message']];
        }
        
        // Validation de la difficulté
        $diffValidation = self::validateDifficulty($difficulte);
        if (!$diffValidation['valid']) {
            return ['success' => false, 'message' => $diffValidation['message']];
        }
        
        // Validation de l'éco-score
        $ecoValidation = self::validateEcoScore($ecoScore);
        if (!$ecoValidation['valid']) {
            return ['success' => false, 'message' => $ecoValidation['message']];
        }
        
        // Validation des ingrédients
        $ingredientsValidation = self::validateIngredients($ingredients);
        if (!$ingredientsValidation['valid']) {
            return ['success' => false, 'message' => $ingredientsValidation['message']];
        }
        
        $newId = self::getRecipeInstance()->create($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $newId !== false, 'id' => $newId, 'message' => $newId ? 'Recette creee avec succes' : 'Erreur lors de la creation'];
    }

    // Update recipe avec validation
    public static function updateRecipe($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        // Validation du titre
        $titleValidation = self::validateTitle($titre);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'message' => $titleValidation['message']];
        }
        
        // Validation des instructions
        $instructionsValidation = self::validateInstructions($instruction);
        if (!$instructionsValidation['valid']) {
            return ['success' => false, 'message' => $instructionsValidation['message']];
        }
        
        // Validation du temps
        $timeValidation = self::validateTime($temp);
        if (!$timeValidation['valid']) {
            return ['success' => false, 'message' => $timeValidation['message']];
        }
        
        // Validation de la difficulté
        $diffValidation = self::validateDifficulty($difficulte);
        if (!$diffValidation['valid']) {
            return ['success' => false, 'message' => $diffValidation['message']];
        }
        
        // Validation de l'éco-score
        $ecoValidation = self::validateEcoScore($ecoScore);
        if (!$ecoValidation['valid']) {
            return ['success' => false, 'message' => $ecoValidation['message']];
        }
        
        // Validation des ingrédients
        $ingredientsValidation = self::validateIngredients($ingredients);
        if (!$ingredientsValidation['valid']) {
            return ['success' => false, 'message' => $ingredientsValidation['message']];
        }
        
        $success = self::getRecipeInstance()->update($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $success, 'message' => $success ? 'Recette mise a jour avec succes' : 'Recette non trouvee'];
    }

    // Delete recipe
    public static function deleteRecipe($id) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => 'ID de recette invalide'];
        }
        error_log("deleteRecipe called with ID: {$id}");
        $success = self::getRecipeInstance()->delete($id);
        error_log("deleteRecipe result: " . ($success ? 'true' : 'false'));
        return ['success' => $success, 'message' => $success ? 'Recette supprimee avec succes' : 'Echec de la suppression'];
    }

    // Add review to recipe avec validation
    public static function addReview($recipeId, $utilisateur, $note, $commentaire) {
        // Validation de la note
        if (!is_numeric($note) || $note < 1 || $note > 5) {
            return ['success' => false, 'message' => 'La note doit etre comprise entre 1 et 5'];
        }
        
        // Validation du nom d'utilisateur
        if (empty($utilisateur) || trim($utilisateur) === '') {
            $utilisateur = 'Anonyme';
        }
        if (strlen($utilisateur) > 100) {
            $utilisateur = substr($utilisateur, 0, 100);
        }
        
        // Validation du commentaire
        if (empty($commentaire) || trim($commentaire) === '') {
            return ['success' => false, 'message' => 'Le commentaire est requis'];
        }
        if (strlen($commentaire) > 1000) {
            return ['success' => false, 'message' => 'Le commentaire ne peut pas depasser 1000 caracteres'];
        }
        
        // Nettoyage des données
        $utilisateur = htmlspecialchars(trim($utilisateur), ENT_QUOTES, 'UTF-8');
        $commentaire = htmlspecialchars(trim($commentaire), ENT_QUOTES, 'UTF-8');
        
        $success = self::getRecipeInstance()->addReview($recipeId, $utilisateur, $note, $commentaire);
        return ['success' => $success, 'message' => $success ? 'Avis ajoute avec succes' : 'Erreur lors de l\'ajout de l\'avis'];
    }

    // Delete review
    public static function deleteReview($recipeId, $reviewId) {
        if (!is_numeric($recipeId) || $recipeId <= 0) {
            return ['success' => false, 'message' => 'ID de recette invalide'];
        }
        if (!is_numeric($reviewId) || $reviewId <= 0) {
            return ['success' => false, 'message' => 'ID d\'avis invalide'];
        }
        $success = self::getRecipeInstance()->deleteReview($recipeId, $reviewId);
        return ['success' => $success, 'message' => $success ? 'Avis supprime avec succes' : 'Echec de la suppression'];
    }

    // Get dashboard stats
    public static function getDashboardStats() {
        $db = self::getDB();
        
        return [
            'recettes' => $db->fetchOne("SELECT COUNT(*) as c FROM recettes WHERE est_actif = TRUE")['c'],
            'ingredients' => $db->fetchOne("SELECT COUNT(*) as c FROM ingredients")['c'],
            'avis' => $db->fetchOne("SELECT COUNT(*) as c FROM avis")['c']
        ];
    }

    // Get top recipes by rating
    public static function getTopRecipes($limit = 5) {
        $recipes = self::getAllRecipes();
        $recipeswithRating = [];
        
        foreach ($recipes as $recipe) {
            $avgRating = self::getRecipeInstance()->getAverageRating($recipe['id']);
            $recipeswithRating[] = [
                'id' => $recipe['id'],
                'titre' => $recipe['titre'],
                'avgNote' => $avgRating,
                'avis' => $recipe['nombre_avis'] ?? 0
            ];
        }
        
        usort($recipeswithRating, function($a, $b) {
            return ($b['avgNote'] ?? 0) <=> ($a['avgNote'] ?? 0);
        });
        
        return array_slice($recipeswithRating, 0, $limit);
    }

    // Get recently reviewed recipes
    public static function getRecentReviews($limit = 5) {
        $db = self::getDB();
        $sql = "SELECT a.*, r.titre FROM avis a 
                JOIN recettes r ON a.recette_id = r.id 
                ORDER BY a.date_creation DESC 
                LIMIT :limit";
        
        $stmt = $db->query($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>