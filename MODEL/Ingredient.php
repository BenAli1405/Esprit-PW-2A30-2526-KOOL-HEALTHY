<?php
// ========== INGREDIENT MODEL ==========
require_once __DIR__ . '/../DATABASE/Database.php';

class Ingredient {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get all ingredients (includes nutritional data)
    public function getAll() {
    $sql = "SELECT id, nom, calories, eco_score,
                   proteines, glucides, lipides, fibres, sel
            FROM ingredients ORDER BY nom ASC";
    return $this->db->fetchAll($sql);
}

    // Get ingredient by ID
    public function getById($id) {
    $sql = "SELECT id, nom, calories, eco_score,
                   proteines, glucides, lipides, fibres, sel
            FROM ingredients WHERE id = :id";
    return $this->db->fetchOne($sql, [':id' => $id]);
}

    // Get ingredient name by ID
    public function getNameById($id) {
        $ing = $this->getById($id);
        return $ing ? $ing['nom'] : null;
    }

    // Create ingredient (with optional nutritional data)
    public function create($nom, $calories = null, $ecoScore = 'A',
                           $proteines = 0, $glucides = 0, $lipides = 0,
                           $fibres = 0, $sel = 0) {
        try {
            $sql = "INSERT INTO ingredients
                        (nom, calories, eco_score, proteines, glucides, lipides, fibres, sel)
                    VALUES
                        (:nom, :calories, :eco_score, :proteines, :glucides, :lipides, :fibres, :sel)";
            $this->db->execute($sql, [
                ':nom'       => $nom,
                ':calories'  => $calories,
                ':eco_score' => $ecoScore,
                ':proteines' => (float)$proteines,
                ':glucides'  => (float)$glucides,
                ':lipides'   => (float)$lipides,
                ':fibres'    => (float)$fibres,
                ':sel'       => (float)$sel,
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Update ingredient (with optional nutritional data)
    public function update($id, $nom, $calories = null, $ecoScore = 'A',
                           $proteines = 0, $glucides = 0, $lipides = 0,
                           $fibres = 0, $sel = 0) {
        try {
            $sql = "UPDATE ingredients
                    SET nom = :nom, calories = :calories, eco_score = :eco_score,
                        proteines = :proteines, glucides = :glucides, lipides = :lipides,
                        fibres = :fibres, sel = :sel,
                        date_modification = NOW()
                    WHERE id = :id";
            return $this->db->execute($sql, [
                ':id'        => $id,
                ':nom'       => $nom,
                ':calories'  => $calories,
                ':eco_score' => $ecoScore,
                ':proteines' => (float)$proteines,
                ':glucides'  => (float)$glucides,
                ':lipides'   => (float)$lipides,
                ':fibres'    => (float)$fibres,
                ':sel'       => (float)$sel,
            ]);
        } catch (Exception $e) {
            error_log("Error updating ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Delete ingredient (removes junction rows first to respect FK)
    public function delete($id) {
        try {
            error_log("Deleting ingredient ID: $id");
            $this->db->execute("DELETE FROM recette_ingredient WHERE ingredient_id = :id", [':id' => $id]);
            $result = $this->db->execute("DELETE FROM ingredients WHERE id = :id", [':id' => $id]);
            error_log("Deleted ingredient $id, result: " . ($result ? 'true' : 'false'));
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting ingredient: " . $e->getMessage());
            return false;
        }
    }

    // Get usage count (how many recipes use this ingredient)
    public function getUsageCount($id) {
        $sql = "SELECT COUNT(*) as c FROM recette_ingredient WHERE ingredient_id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $id]);
        return $result['c'];
    }

    // Get top ingredients by recipe usage
    public function getTop($limit = 5) {
    $sql = "SELECT i.id, i.nom, i.calories, i.eco_score,
                   i.proteines, i.glucides, i.lipides, i.fibres, i.sel,
                   COUNT(ri.recette_id) as usage_count
            FROM ingredients i
            LEFT JOIN recette_ingredient ri ON i.id = ri.ingredient_id
            GROUP BY i.id
            ORDER BY usage_count DESC
            LIMIT :limit";
    // ... rest of the code
}

    // Search ingredients by name
    public function search($keyword) {
    $sql = "SELECT id, nom, calories, eco_score,
                   proteines, glucides, lipides, fibres, sel
            FROM ingredients
            WHERE nom LIKE :keyword
            ORDER BY nom ASC";
    return $this->db->fetchAll($sql, [':keyword' => '%' . $keyword . '%']);
}
}
?>