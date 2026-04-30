<?php
$db = new PDO(
    'mysql:host=localhost;dbname=web;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "<h2>Checking Ingredient Deletion Issue</h2>";

// Check ingredients table structure
echo "<h3>Ingredients Table Structure:</h3>";
$cols = $db->query("DESCRIBE ingredients");
echo "<pre>";
while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Key'] == 'PRI' ? " PRIMARY" : "") . "\n";
}
echo "</pre>";

// Check foreign key constraints
echo "<h3>Foreign Key Constraints:</h3>";
$fks = $db->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME='recette_ingredient' AND REFERENCED_TABLE_NAME IS NOT NULL");
echo "<pre>";
while ($fk = $fks->fetch(PDO::FETCH_ASSOC)) {
    echo "Table: " . $fk['TABLE_NAME'] . "\n";
    echo "Column: " . $fk['COLUMN_NAME'] . "\n";
    echo "References: " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "\n";
    echo "---\n";
}
echo "</pre>";

// Check which recipes use ingredient 1
echo "<h3>Recipes Using Ingredient 1:</h3>";
$recipes = $db->query("
    SELECT r.id, r.titre 
    FROM recettes r
    JOIN recette_ingredient ri ON r.id = ri.recette_id
    WHERE ri.ingredient_id = 1
");
while ($recipe = $recipes->fetch(PDO::FETCH_ASSOC)) {
    echo "Recipe ID " . $recipe['id'] . ": " . $recipe['titre'] . "\n";
}

// Try to delete ingredient 1 step by step
echo "<h3>Testing Delete Steps:</h3>";

try {
    echo "1. Deleting from recette_ingredient... ";
    $db->exec("DELETE FROM recette_ingredient WHERE ingredient_id = 1");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "2. Deleting from ingredients... ";
    $db->exec("DELETE FROM ingredients WHERE id = 1");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Check if ingredient 1 still exists
$check = $db->query("SELECT COUNT(*) as c FROM ingredients WHERE id = 1")->fetch(PDO::FETCH_ASSOC)['c'];
echo "\nIngredient 1 still exists: " . ($check > 0 ? "YES ❌" : "NO ✅") . "\n";
?>
