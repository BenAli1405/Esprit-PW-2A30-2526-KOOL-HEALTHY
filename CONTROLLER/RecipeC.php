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

    // Get all recipes
    public static function getAllRecipes() {
        return self::getRecipeInstance()->getAll();
    }

    // Get recipe by ID with ingredients
    public static function getRecipeById($id) {
        return self::getRecipeInstance()->getWithIngredients($id);
    }

    // Create new recipe
    public static function createRecipe($utilisateurId, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        // Validation
        if (empty($titre) || !is_numeric($temp)) {
            return ['success' => false, 'message' => 'Données invalides'];
        }
        
        $newId = self::getRecipeInstance()->create($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $newId !== false, 'id' => $newId, 'message' => $newId ? 'Recette créée' : 'Erreur'];
    }

    // Update recipe
    public static function updateRecipe($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        if (empty($titre) || !is_numeric($temp)) {
            return ['success' => false, 'message' => 'Données invalides'];
        }
        
        $success = self::getRecipeInstance()->update($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $success, 'message' => $success ? 'Recette mise à jour' : 'Recette non trouvée'];
    }

    // Delete recipe
    public static function deleteRecipe($id) {
        error_log("deleteRecipe called with ID: {$id}");
        $success = self::getRecipeInstance()->delete($id);
        error_log("deleteRecipe result: " . ($success ? 'true' : 'false'));
        return ['success' => $success, 'message' => $success ? 'Recette supprimée' : 'Échec de la suppression'];
    }

    // Apply filters
    public static function filterRecipes($searchTerm = '', $difficulte = '', $ecoScore = '', $temp = null) {
        $recipes = self::getAllRecipes();
        $filtered = [];
        
        foreach ($recipes as $recipe) {
            $matchSearch = empty($searchTerm) || 
                          strpos(strtolower($recipe['titre']), strtolower($searchTerm)) !== false;
            $matchDiff = empty($difficulte) || $recipe['difficulte'] === $difficulte;
            $matchEco = empty($ecoScore) || $recipe['eco_score'] === $ecoScore;
            $matchTime = $temp === null || $recipe['temps_preparation'] <= $temp;
            
            if ($matchSearch && $matchDiff && $matchEco && $matchTime) {
                $filtered[] = $recipe;
            }
        }
        
        return $filtered;
    }

    // Add review to recipe
    public static function addReview($recipeId, $utilisateur, $note, $commentaire) {
        if (!is_numeric($note) || $note < 1 || $note > 5) {
            return ['success' => false, 'message' => 'Note invalide'];
        }
        if (empty($utilisateur)) {
            $utilisateur = 'Anonyme';
        }
        
        $success = self::getRecipeInstance()->addReview($recipeId, $utilisateur, $note, $commentaire);
        return ['success' => $success, 'message' => $success ? 'Avis ajouté' : 'Erreur'];
    }

    // Delete review
    public static function deleteReview($recipeId, $reviewId) {
        $success = self::getRecipeInstance()->deleteReview($recipeId, $reviewId);
        return ['success' => $success, 'message' => $success ? 'Avis supprimé' : 'Échec'];
    }

    // Get dashboard stats
    public static function getDashboardStats() {
        $db = self::getDB();
        
        return [
            'recettes' => $db->fetchOne("SELECT COUNT(*) as c FROM recettes")['c'],
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

