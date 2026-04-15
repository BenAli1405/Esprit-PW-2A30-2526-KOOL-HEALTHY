<?php
// ========== INGREDIENT MODEL ==========
require_once __DIR__ . '/../DATABASE/Database.php';

class Ingredient {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get all ingredients
    public function getAll() {
        $sql = "SELECT * FROM ingredients ORDER BY nom ASC";
        return $this->db->fetchAll($sql);
    }

    // Get ingredient by ID
    public function getById($id) {
        $sql = "SELECT * FROM ingredients WHERE id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }

    // Get ingredient name by ID
    public function getNameById($id) {
        $ing = $this->getById($id);
        return $ing ? $ing['nom'] : null;
    }

    // Create ingredient
    public function create($nom, $calories = null, $ecoScore = 'A') {
        try {
            $sql = "INSERT INTO ingredients (nom, calories, eco_score)
                    VALUES (:nom, :calories, :eco_score)";
            $this->db->execute($sql, [
                ':nom' => $nom,
                ':calories' => $calories,
                ':eco_score' => $ecoScore
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Update ingredient
    public function update($id, $nom, $calories = null, $ecoScore = 'A') {
        try {
            $sql = "UPDATE ingredients 
                    SET nom = :nom, calories = :calories, eco_score = :eco_score, date_modification = NOW()
                    WHERE id = :id";
            return $this->db->execute($sql, [
                ':id' => $id,
                ':nom' => $nom,
                ':calories' => $calories,
                ':eco_score' => $ecoScore
            ]);
        } catch (Exception $e) {
            error_log("Error updating ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Delete ingredient
    public function delete($id) {
        try {
            error_log("Deleting ingredient ID: $id");
            
            // First, delete all recipe-ingredient links (junction table)
            $sql1 = "DELETE FROM recette_ingredient WHERE ingredient_id = :id";
            $this->db->execute($sql1, [':id' => $id]);
            error_log("Deleted recipe_ingredient links for ingredient $id");
            
            // Then delete the ingredient itself
            $sql2 = "DELETE FROM ingredients WHERE id = :id";
            $result = $this->db->execute($sql2, [':id' => $id]);
            error_log("Deleted ingredient $id, result: " . ($result ? 'true' : 'false'));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Get usage count
    public function getUsageCount($id) {
        $sql = "SELECT COUNT(*) as c FROM recette_ingredient WHERE ingredient_id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $id]);
        return $result['c'];
    }

    // Get top ingredients
    public function getTop($limit = 5) {
        $sql = "SELECT i.*, COUNT(ri.recette_id) as usage_count
                FROM ingredients i
                LEFT JOIN recette_ingredient ri ON i.id = ri.ingredient_id
                WHERE i.est_actif = TRUE
                GROUP BY i.id
                ORDER BY usage_count DESC
                LIMIT :limit";
        
        $stmt = $this->db->query($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search ingredients
    public function search($keyword) {
        $sql = "SELECT * FROM ingredients 
                WHERE est_actif = TRUE AND nom LIKE :keyword
                ORDER BY nom ASC";
        return $this->db->fetchAll($sql, [':keyword' => '%' . $keyword . '%']);
    }
}

// Static methods for compatibility with controllers
class IngredientC_DB {
    private static $ingredient = null;

    private static function getInstance() {
        if (self::$ingredient === null) {
            self::$ingredient = new Ingredient();
        }
        return self::$ingredient;
    }

    public static function getAllIngredients() {
        return self::getInstance()->getAll();
    }

    public static function getIngredientById($id) {
        return self::getInstance()->getById($id);
    }

    public static function createIngredient($nom, $calories = null, $ecoScore = 'A') {
        $id = self::getInstance()->create($nom, $calories, $ecoScore);
        return ['success' => $id !== false, 'id' => $id, 'message' => $id ? 'Ingrédient créé' : 'Erreur'];
    }

    public static function updateIngredient($id, $nom, $calories = null, $ecoScore = 'A') {
        $success = self::getInstance()->update($id, $nom, $calories, $ecoScore);
        return ['success' => $success, 'message' => $success ? 'Ingrédient mis à jour' : 'Erreur'];
    }

    public static function deleteIngredient($id) {
        $success = self::getInstance()->delete($id);
        return ['success' => $success, 'message' => $success ? 'Ingrédient supprimé' : 'Erreur'];
    }

    public static function getUsageCount($id) {
        return self::getInstance()->getUsageCount($id);
    }

    public static function getTop($limit = 5) {
        return self::getInstance()->getTop($limit);
    }
}
?>
