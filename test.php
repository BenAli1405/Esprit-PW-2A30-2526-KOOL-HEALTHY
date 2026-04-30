<?php
// Simple test page to verify everything is working

echo "<h1>System Test</h1>";

// Test 1: PHP working
echo "<p>✅ PHP is working</p>";

// Test 2: Database connection
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=web;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✅ Database connected</p>";
    
    // Test 3: Check recipes count
    $result = $db->query("SELECT COUNT(*) as c FROM recettes WHERE est_actif = TRUE");
    $recipes = $result->fetch(PDO::FETCH_ASSOC)['c'];
    echo "<p>✅ Recipes in database: <strong>$recipes</strong></p>";
    
    // Test 4: Check ingredients
    $result = $db->query("SELECT COUNT(*) as c FROM ingredients");
    $ingredients = $result->fetch(PDO::FETCH_ASSOC)['c'];
    echo "<p>✅ Ingredients in database: <strong>$ingredients</strong></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 5: Try to fetch recipes
echo "<h2>Testing Recipe Fetch</h2>";
try {
    require 'MODEL/Recipe.php';
    $recipe = new Recipe();
    $recipes = $recipe->getAll();
    echo "<p>✅ Recipe model working - found " . count($recipes) . " recipes</p>";
    
    if (count($recipes) > 0) {
        echo "<p>First recipe: " . $recipes[0]['titre'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Recipe model error: " . $e->getMessage() . "</p>";
}

// Test 6: Test API endpoint
echo "<h2>Testing API</h2>";
echo "<p>Try: <a href='INDEX.php?action=getAllRecipes' target='_blank'>getAllRecipes API</a></p>";
echo "<p>Try: <a href='INDEX.php?action=getAllIngredients' target='_blank'>getAllIngredients API</a></p>";
