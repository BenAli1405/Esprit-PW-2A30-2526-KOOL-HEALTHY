<?php
// ========== RECIPE MODEL ==========
require_once __DIR__ . '/../DATABASE/Database.php';

class Recipe {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get all recipes with their ingredients and reviews
    public function getAll() {
        $sql = "SELECT r.id, r.titre, r.instruction, r.temps_preparation,
                       r.difficulte, r.eco_score, r.nombre_portions, r.date_creation,
                       COUNT(DISTINCT a.id) as nombre_avis,
                       COALESCE(AVG(a.note), 0) as note_moyenne
                FROM recettes r
                LEFT JOIN avis a ON r.id = a.recette_id
                WHERE r.est_actif = TRUE
                GROUP BY r.id, r.titre, r.instruction, r.temps_preparation,
                         r.difficulte, r.eco_score, r.nombre_portions, r.date_creation
                ORDER BY r.date_creation DESC";

        $recipes = $this->db->fetchAll($sql);

        foreach ($recipes as &$recipe) {
            // Fetch ingredients (including nutritional data)
            $sql_ing = "SELECT ri.ingredient_id, ri.quantite, ri.unite, ri.ordre,
                               i.nom, i.calories, i.eco_score,
                               i.proteines, i.glucides, i.lipides, i.fibres, i.sel
                        FROM recette_ingredient ri
                        JOIN ingredients i ON ri.ingredient_id = i.id
                        WHERE ri.recette_id = :id
                        ORDER BY ri.ordre";
            $recipe['ingredients'] = $this->db->fetchAll($sql_ing, [':id' => $recipe['id']]);

            // Fetch reviews
            $sql_avis = "SELECT * FROM avis WHERE recette_id = :id ORDER BY date_creation DESC";
            $recipe['avis'] = $this->db->fetchAll($sql_avis, [':id' => $recipe['id']]);

            // Attach computed nutrition
            $recipe['nutrition'] = $this->computeNutrition($recipe['ingredients']);
            $recipe['labels']    = $this->computeLabels($recipe['nutrition']);
        }

        return $recipes;
    }

    // Get recipe by ID
    public function getById($id) {
        $sql = "SELECT r.id, r.titre, r.instruction, r.temps_preparation,
                       r.difficulte, r.eco_score, r.nombre_portions, r.date_creation,
                       COUNT(a.id) as nombre_avis,
                       COALESCE(AVG(a.note), 0) as note_moyenne
                FROM recettes r
                LEFT JOIN avis a ON r.id = a.recette_id
                WHERE r.id = :id
                GROUP BY r.id, r.titre, r.instruction, r.temps_preparation,
                         r.difficulte, r.eco_score, r.nombre_portions, r.date_creation";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }

    // Get recipe with ingredients, reviews and nutrition
    public function getWithIngredients($id) {
        $recipe = $this->getById($id);
        if (!$recipe) return null;

        $sql = "SELECT ri.ingredient_id, ri.quantite, ri.unite, ri.ordre,
                       i.nom, i.calories, i.eco_score,
                       i.proteines, i.glucides, i.lipides, i.fibres, i.sel
                FROM recette_ingredient ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                WHERE ri.recette_id = :id
                ORDER BY ri.ordre";
        $recipe['ingredients'] = $this->db->fetchAll($sql, [':id' => $id]);

        $sql_avis = "SELECT * FROM avis WHERE recette_id = :id ORDER BY date_creation DESC";
        $recipe['avis'] = $this->db->fetchAll($sql_avis, [':id' => $id]);

        $recipe['nutrition'] = $this->computeNutrition($recipe['ingredients']);
        $recipe['labels']    = $this->computeLabels($recipe['nutrition']);

        return $recipe;
    }

    // ========== NUTRITION ENGINE ==========

    /**
     * Calculate total nutrition for a recipe from its ingredient rows.
     * Each ingredient row must have: quantite (grams), proteines, glucides,
     * lipides, fibres, sel (all per 100g).
     *
     * Returns: [ calories, proteines, glucides, lipides, fibres, sel ]
     */
    public function computeNutrition(array $ingredients): array {
        $totals = [
            'calories'  => 0.0,
            'proteines' => 0.0,
            'glucides'  => 0.0,
            'lipides'   => 0.0,
            'fibres'    => 0.0,
            'sel'       => 0.0,
        ];

        foreach ($ingredients as $ing) {
            $qty = (float)($ing['quantite'] ?? 0);
            if ($qty <= 0) continue;

            $factor = $qty / 100.0;

            // Parse "139kcal/100g" or "139kcal" → 139
            $kcal = 0;
            if (!empty($ing['calories'])) {
                if (preg_match('/(\d+(?:\.\d+)?)/', $ing['calories'], $m)) {
                    $kcal = (float)$m[1];
                }
            } else {
                // Fallback: estimate from macros (4/4/9 kcal rule)
                $kcal = ((float)($ing['proteines'] ?? 0) * 4)
                      + ((float)($ing['glucides']  ?? 0) * 4)
                      + ((float)($ing['lipides']   ?? 0) * 9);
            }

            $totals['calories']  += $kcal                        * $factor;
            $totals['proteines'] += (float)($ing['proteines'] ?? 0) * $factor;
            $totals['glucides']  += (float)($ing['glucides']  ?? 0) * $factor;
            $totals['lipides']   += (float)($ing['lipides']   ?? 0) * $factor;
            $totals['fibres']    += (float)($ing['fibres']    ?? 0) * $factor;
            $totals['sel']       += (float)($ing['sel']       ?? 0) * $factor;
        }

        // Round to 1 decimal
        foreach ($totals as &$v) {
            $v = round($v, 1);
        }

        return $totals;
    }

    /**
     * Derive nutrition labels from computed totals.
     * Returns an array of label strings.
     */
    public function computeLabels(array $nutrition): array {
        $labels = [];

        if ($nutrition['proteines'] >= 20)  $labels[] = 'Riche en protéines';
        if ($nutrition['proteines'] >= 10 && $nutrition['proteines'] < 20)
                                             $labels[] = 'Source de protéines';
        if ($nutrition['glucides']  <= 30)  $labels[] = 'Low carb';
        if ($nutrition['fibres']    >= 10)  $labels[] = 'Riche en fibres';
        if ($nutrition['fibres']    >= 5 && $nutrition['fibres'] < 10)
                                             $labels[] = 'Source de fibres';
        if ($nutrition['calories']  <= 300) $labels[] = 'Léger';
        if ($nutrition['lipides']   <= 5)   $labels[] = 'Faible en graisses';

        // Balanced label: no single macro dominates
        $isBalanced = $nutrition['proteines'] >= 8
                   && $nutrition['glucides']  >= 15
                   && $nutrition['lipides']   >= 3
                   && $nutrition['calories']  <= 600;
        if ($isBalanced) $labels[] = 'Équilibré';

        return $labels;
    }

    /**
     * Return which objectives this recipe is suited for, and which it isn't.
     * Objectives: perte_de_poids | musculation | equilibre
     */
    public function computeGoalMatch(array $nutrition): array {
        $match    = [];
        $mismatch = [];

        // Perte de poids: < 400 kcal
        if ($nutrition['calories'] < 400) {
            $match[]    = 'Perte de poids';
        } else {
            $mismatch[] = ['goal' => 'Perte de poids', 'reason' => 'Trop calorique (' . $nutrition['calories'] . ' kcal)'];
        }

        // Musculation: >= 20g proteins
        if ($nutrition['proteines'] >= 20) {
            $match[]    = 'Musculation';
        } else {
            $mismatch[] = ['goal' => 'Musculation', 'reason' => 'Pas assez de protéines (' . $nutrition['proteines'] . 'g)'];
        }

        // Équilibre: 300–600 kcal + decent macros
        if ($nutrition['calories'] >= 300 && $nutrition['calories'] <= 600
            && $nutrition['proteines'] >= 8 && $nutrition['glucides'] >= 15) {
            $match[]    = 'Équilibre';
        } else {
            $mismatch[] = ['goal' => 'Équilibre', 'reason' => 'Macros déséquilibrés'];
        }

        return ['recommande' => $match, 'deconseille' => $mismatch];
    }

    // ========== CRUD ==========

    public function create($titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        try {
            $sql = "INSERT INTO recettes (titre, instruction, temps_preparation, difficulte, eco_score)
                    VALUES (:titre, :instruction, :temps, :difficulte, :eco_score)";
            $this->db->execute($sql, [
                ':titre'      => $titre,
                ':instruction'=> $instruction,
                ':temps'      => $temp,
                ':difficulte' => $difficulte,
                ':eco_score'  => $ecoScore,
            ]);
            $recipeId = $this->db->lastInsertId();

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

    public function update($id, $titre, $instruction, $temp, $difficulte, $ecoScore, $ingredients) {
        try {
            $sql = "UPDATE recettes
                    SET titre = :titre, instruction = :instruction, temps_preparation = :temps,
                        difficulte = :difficulte, eco_score = :eco_score, date_modification = NOW()
                    WHERE id = :id";
            $this->db->execute($sql, [
                ':id'         => $id,
                ':titre'      => $titre,
                ':instruction'=> $instruction,
                ':temps'      => $temp,
                ':difficulte' => $difficulte,
                ':eco_score'  => $ecoScore,
            ]);

            $this->deleteIngredients($id);

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

    public function delete($id) {
        try {
            $this->db->execute("DELETE FROM avis WHERE recette_id = :id", [':id' => $id]);
            $this->db->execute("DELETE FROM recette_ingredient WHERE recette_id = :id", [':id' => $id]);
            return $this->db->execute("DELETE FROM recettes WHERE id = :id", [':id' => $id]);
        } catch (Exception $e) {
            error_log("Error deleting recipe: " . $e->getMessage());
            return false;
        }
    }

    private function addIngredient($recipeId, $ingredientId, $qty, $unite, $ordre) {
        $sql = "INSERT INTO recette_ingredient (recette_id, ingredient_id, quantite, unite, ordre)
                VALUES (:recette_id, :ingredient_id, :qty, :unite, :ordre)";
        return $this->db->execute($sql, [
            ':recette_id'   => $recipeId,
            ':ingredient_id'=> $ingredientId,
            ':qty'          => $qty,
            ':unite'        => $unite,
            ':ordre'        => $ordre,
        ]);
    }

    private function deleteIngredients($recipeId) {
        return $this->db->execute("DELETE FROM recette_ingredient WHERE recette_id = :id", [':id' => $recipeId]);
    }

    public function addReview($recipeId, $utilisateur, $note, $commentaire) {
        $sql = "INSERT INTO avis (recette_id, utilisateur_nom, note, commentaire)
                VALUES (:recette_id, :utilisateur, :note, :commentaire)";
        return $this->db->execute($sql, [
            ':recette_id' => $recipeId,
            ':utilisateur'=> $utilisateur,
            ':note'       => $note,
            ':commentaire'=> $commentaire,
        ]);
    }

    public function updateReview($reviewId, $note, $commentaire) {
        $sql = "UPDATE avis SET note = :note, commentaire = :commentaire WHERE id = :id";
        return $this->db->execute($sql, [
            ':id'          => $reviewId,
            ':note'        => $note,
            ':commentaire' => $commentaire,
        ]);
    }

    public function deleteReview($recipeId, $reviewId) {
        $sql = "DELETE FROM avis WHERE id = :id AND recette_id = :recette_id";
        return $this->db->execute($sql, [
            ':id'        => $reviewId,
            ':recette_id'=> $recipeId,
        ]);
    }

    public function getAverageRating($recipeId) {
        $sql    = "SELECT AVG(note) as moyenne FROM avis WHERE recette_id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $recipeId]);
        return $result['moyenne'] ? round($result['moyenne'], 1) : null;
    }

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
?>