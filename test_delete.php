<?php
require_once 'config.php';
require_once CONTROLLER_PATH . 'RecipeC.php';
require_once CONTROLLER_PATH . 'IngredientC.php';

echo "<h1>Test Delete Operations</h1>";

// Get database connection
$db = new PDO(
    'mysql:host=localhost;dbname=web;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Test 1: Check recipes table
echo "<h2>Recipes Table Structure</h2>";
$cols = $db->query("DESCRIBE recettes");
echo "<pre>";
while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "</pre>";

// Test 2: Count total recipes
$total = $db->query("SELECT COUNT(*) as c FROM recettes")->fetch(PDO::FETCH_ASSOC)['c'];
echo "<p>Total recipes in DB: <strong>$total</strong></p>";

$active = $db->query("SELECT COUNT(*) as c FROM recettes WHERE est_actif = TRUE")->fetch(PDO::FETCH_ASSOC)['c'];
echo "<p>Active recipes: <strong>$active</strong></p>";

$inactive = $db->query("SELECT COUNT(*) as c FROM recettes WHERE est_actif = FALSE")->fetch(PDO::FETCH_ASSOC)['c'];
echo "<p>Inactive (deleted) recipes: <strong>$inactive</strong></p>";

// Test 3: Try to delete recipe ID 1
echo "<h2>Testing Delete Recipe ID 1</h2>";
$result = RecipeC::deleteRecipe(1);
echo "<pre>";
var_dump($result);
echo "</pre>";

// Test 4: Check if it worked
$check = $db->query("SELECT COUNT(*) as c FROM recettes WHERE id = 1 AND est_actif = FALSE")->fetch(PDO::FETCH_ASSOC)['c'];
echo "<p>Recipe 1 marked as deleted: " . ($check > 0 ? "✅ YES" : "❌ NO") . "</p>";

// Test 5: Test ingredient delete
echo "<h2>Testing Delete Ingredient ID 1</h2>";
$result = IngredientC::deleteIngredient(1);
echo "<pre>";
var_dump($result);
echo "</pre>";

// Test 6: Check if it worked
$check = $db->query("SELECT COUNT(*) as c FROM ingredients WHERE id = 1")->fetch(PDO::FETCH_ASSOC)['c'];
echo "<p>Ingredient 1 deleted from DB: " . ($check == 0 ? "✅ YES" : "❌ NO (still exists)") . "</p>";
?>
