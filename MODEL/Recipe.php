<?php
// ========== RECIPE MODEL ==========
require_once __DIR__ . '/../DATABASE/Database.php';

class Recipe {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Dans MODEL/Recipe.php - remplacer la méthode getAll()
public function getAll() {
    $sql = "SELECT r.id, r.titre, r.instruction, r.temps_preparation, r.difficulte, r.eco_score, r.nombre_portions, r.date_creation,
            COUNT(DISTINCT a.id) as nombre_avis, COALESCE(AVG(a.note), 0) as note_moyenne
            FROM recettes r
            LEFT JOIN avis a ON r.id = a.recette_id
            WHERE r.est_actif = TRUE
            GROUP BY r.id, r.titre, r.instruction, r.temps_preparation, r.difficulte, r.eco_score, r.nombre_portions, r.date_creation
            ORDER BY r.date_creation DESC";
    
    $recipes = $this->db->fetchAll($sql);
    
    // Ajouter les ingrédients et avis pour chaque recette
    foreach ($recipes as &$recipe) {
        // Récupérer les ingrédients
        $sql_ing = "SELECT ri.*, i.nom, i.calories, i.eco_score
                    FROM recette_ingredient ri
                    JOIN ingredients i ON ri.ingredient_id = i.id
                    WHERE ri.recette_id = :id
                    ORDER BY ri.ordre";
        $recipe['ingredients'] = $this->db->fetchAll($sql_ing, [':id' => $recipe['id']]);
        
        // Récupérer les avis
        $sql_avis = "SELECT * FROM avis WHERE recette_id = :id ORDER BY date_creation DESC";
        $recipe['avis'] = $this->db->fetchAll($sql_avis, [':id' => $recipe['id']]);
    }
    
    return $recipes;
}

    // Get recipe by ID
    public function getById($id) {
        $sql = "SELECT r.id, r.titre, r.instruction, r.temps_preparation, r.difficulte, r.eco_score, r.nombre_portions, r.date_creation,
                COUNT(a.id) as nombre_avis, COALESCE(AVG(a.note), 0) as note_moyenne
                FROM recettes r
                LEFT JOIN avis a ON r.id = a.recette_id
                WHERE r.id = :id
                GROUP BY r.id, r.titre, r.instruction, r.temps_preparation, r.difficulte, r.eco_score, r.nombre_portions, r.date_creation";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }

    // Get recipe with ingredients
    public function getWithIngredients($id) {
        $recipe = $this->getById($id);
        if (!$recipe) return null;

        $sql = "SELECT ri.*, i.nom, i.calories, i.eco_score
                FROM recette_ingredient ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                WHERE ri.recette_id = :id
                ORDER BY ri.ordre";
        $recipe['ingredients'] = $this->db->fetchAll($sql, [':id' => $id]);

        // Get reviews
        $sql = "SELECT * FROM avis WHERE recette_id = :id ORDER BY date_creation DESC";
        $recipe['avis'] = $this->db->fetchAll($sql, [':id' => $id]);

        return $recipe;
    }

    // Create recipe
    public function create($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        try {
            $sql = "INSERT INTO recettes (titre, instruction, temps_preparation, difficulte, eco_score)
                    VALUES (:titre, :instruction, :temps, :difficulte, :eco_score)";
            $this->db->execute($sql, [
                ':titre' => $titre,
                ':instruction' => $instruction,
                ':temps' => $temp,
                ':difficulte' => $difficulte,
                ':eco_score' => $ecoScore
            ]);

            $recipeId = $this->db->lastInsertId();

            // Add ingredients
            if (is_array($ingredients)) {
                foreach ($ingredients as $index => $ing) {
                    $this->addIngredient($recipeId, $ing['idIng'], $ing['qty'], $ing['unite'], $index);
                }
            }

            return $recipeId;
        } catch (Exception $e) {
            error_log("Error creating recipe: " . $e->getMessage());
            return false;
        }
    }

    // Update recipe
    public function update($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        try {
            $sql = "UPDATE recettes 
                    SET titre = :titre, instruction = :instruction, temps_preparation = :temps,
                        difficulte = :difficulte, eco_score = :eco_score, date_modification = NOW()
                    WHERE id = :id";
            $this->db->execute($sql, [
                ':id' => $id,
                ':titre' => $titre,
                ':instruction' => $instruction,
                ':temps' => $temp,
                ':difficulte' => $difficulte,
                ':eco_score' => $ecoScore
            ]);

            // Delete old ingredients
            $this->deleteIngredients($id);

            // Add new ingredients
            if (is_array($ingredients)) {
                foreach ($ingredients as $index => $ing) {
                    $this->addIngredient($id, $ing['idIng'], $ing['qty'], $ing['unite'], $index);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating recipe: " . $e->getMessage());
            return false;
        }
    }

    // Delete recipe
    public function delete($id) {
        try {
            error_log("DELETE: Hard delete recipe with ID: $id");
            
            // Step 1: Delete all reviews for this recipe
            $sql1 = "DELETE FROM avis WHERE recette_id = :id";
            $this->db->execute($sql1, [':id' => $id]);
            error_log("DELETE: Deleted reviews for recipe $id");
            
            // Step 2: Delete all recipe-ingredient links
            $sql2 = "DELETE FROM recette_ingredient WHERE recette_id = :id";
            $this->db->execute($sql2, [':id' => $id]);
            error_log("DELETE: Deleted recipe_ingredient links for recipe $id");
            
            // Step 3: Delete the recipe itself
            $sql3 = "DELETE FROM recettes WHERE id = :id";
            $result = $this->db->execute($sql3, [':id' => $id]);
            error_log("DELETE: Deleted recipe $id, result: " . ($result ? 'true' : 'false'));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting recipe: " . $e->getMessage());
            return false;
        }
    }

    // Add ingredient to recipe
    private function addIngredient($recipeId, $ingredientId, $qty, $unite, $ordre) {
        $sql = "INSERT INTO recette_ingredient (recette_id, ingredient_id, quantite, unite, ordre)
                VALUES (:recette_id, :ingredient_id, :qty, :unite, :ordre)";
        return $this->db->execute($sql, [
            ':recette_id' => $recipeId,
            ':ingredient_id' => $ingredientId,
            ':qty' => $qty,
            ':unite' => $unite,
            ':ordre' => $ordre
        ]);
    }

    // Delete all ingredients for a recipe
    private function deleteIngredients($recipeId) {
        $sql = "DELETE FROM recette_ingredient WHERE recette_id = :id";
        return $this->db->execute($sql, [':id' => $recipeId]);
    }

    // Add review
    public function addReview($recipeId, $utilisateur, $note, $commentaire) {
        $sql = "INSERT INTO avis (recette_id, utilisateur_nom, note, commentaire)
                VALUES (:recette_id, :utilisateur, :note, :commentaire)";
        return $this->db->execute($sql, [
            ':recette_id' => $recipeId,
            ':utilisateur' => $utilisateur,
            ':note' => $note,
            ':commentaire' => $commentaire
        ]);
    }

    // Delete review
    public function deleteReview($recipeId, $reviewId) {
        $sql = "DELETE FROM avis WHERE id = :id AND recette_id = :recette_id";
        return $this->db->execute($sql, [
            ':id' => $reviewId,
            ':recette_id' => $recipeId
        ]);
    }

    // Get average rating
    public function getAverageRating($recipeId) {
        $sql = "SELECT AVG(note) as moyenne FROM avis WHERE recette_id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $recipeId]);
        return $result['moyenne'] ? round($result['moyenne'], 1) : null;
    }

    // Get top recipes
    public function getTop($limit = 5) {
        $sql = "SELECT r.id, r.titre, AVG(a.note) as note_moyenne, COUNT(a.id) as nombre_avis
                FROM recettes r
                LEFT JOIN avis a ON r.id = a.recette_id
                WHERE r.est_actif = TRUE
                GROUP BY r.id
                ORDER BY note_moyenne DESC, nombre_avis DESC
                LIMIT :limit";
        
        $stmt = $this->db->query($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Static methods for compatibility with controllers
class RecipeC_DB {
    private static $recipe = null;

    private static function getInstance() {
        if (self::$recipe === null) {
            self::$recipe = new Recipe();
        }
        return self::$recipe;
    }

    public static function getAllRecipes() {
        return self::getInstance()->getAll();
    }

    public static function getRecipeById($id) {
        return self::getInstance()->getWithIngredients($id);
    }

    public static function createRecipe($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        $id = self::getInstance()->create($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $id !== false, 'id' => $id, 'message' => $id ? 'Recette créée' : 'Erreur'];
    }

    public static function updateRecipe($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        $success = self::getInstance()->update($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients);
        return ['success' => $success, 'message' => $success ? 'Recette mise à jour' : 'Erreur'];
    }

    public static function deleteRecipe($id) {
        $success = self::getInstance()->delete($id);
        return ['success' => $success, 'message' => $success ? 'Recette supprimée' : 'Erreur'];
    }

    public static function addReview($recipeId, $utilisateur, $note, $commentaire) {
        $success = self::getInstance()->addReview($recipeId, $utilisateur, $note, $commentaire);
        return ['success' => $success, 'message' => $success ? 'Avis ajouté' : 'Erreur'];
    }

    public static function deleteReview($recipeId, $reviewId) {
        $success = self::getInstance()->deleteReview($recipeId, $reviewId);
        return ['success' => $success, 'message' => $success ? 'Avis supprimé' : 'Erreur'];
    }

    public static function getDashboardStats() {
        $recipe = self::getInstance();
        $db = new Database();
        
        return [
            'recettes' => count($recipe->getAll()),
            'ingredients' => count($db->fetchAll("SELECT COUNT(*) as c FROM ingredients")),
            'avis' => $db->fetchOne("SELECT COUNT(*) as c FROM avis")['c']
        ];
    }
}
?>

