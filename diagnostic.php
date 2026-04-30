<?php
// ========== DIAGNOSTIC & TROUBLESHOOTING PAGE ==========
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Kool Healthy - Diagnostic</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Kool Healthy - Diagnostics</h1>";

// 1. Check PHP version
echo "<div class='card'><h2>1. PHP Version</h2>";
echo "<p><span class='success'>✓ PHP " . phpversion() . "</span></p>";
echo "</div>";

// 2. Check required extensions
echo "<div class='card'><h2>2. Required Extensions</h2>";
$extensions = ['PDO', 'pdo_mysql', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p><span class='success'>✓ $ext</span> - Installed</p>";
    } else {
        echo "<p><span class='error'>✗ $ext</span> - NOT installed</p>";
    }
}
echo "</div>";

// 3. Check config
echo "<div class='card'><h2>3. Configuration</h2>";
require_once 'config.php';
echo "<p>DB_HOST: <code>" . DB_HOST . "</code></p>";
echo "<p>DB_USER: <code>" . DB_USER . "</code></p>";
echo "<p>DB_NAME: <code>" . DB_NAME . "</code></p>";
echo "</div>";

// 4. Test Database Connection
echo "<div class='card'><h2>4. Database Connection</h2>";
try {
    $conn = new PDO(
        'mysql:host=' . DB_HOST,
        DB_USER,
        DB_PASS
    );
    echo "<p><span class='success'>✓ Connected to MySQL Server</span></p>";
    
    // Check if database exists
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "<p><span class='success'>✓ Database '" . DB_NAME . "' exists</span></p>";
        
        // Connect to the database
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check tables
        echo "<p><strong>Tables:</strong></p>";
        $tables = ['ingredients', 'recettes', 'recette_ingredient', 'avis'];
        foreach ($tables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p><span class='success'>✓ Table '$table'</span> exists</p>";
            } else {
                echo "<p><span class='error'>✗ Table '$table'</span> NOT found</p>";
            }
        }
        
        // Check data
        echo "<p><strong>Data Count:</strong></p>";
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT COUNT(*) as c FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
            echo "<p>$table: <code>$count</code> records</p>";
        }
        
    } else {
        echo "<p><span class='warning'>⚠ Database '" . DB_NAME . "' does NOT exist</span></p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>
                <li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>
                <li>Copy entire content of <code>kool_healthy.sql</code> file</li>
                <li>Create new query and paste</li>
                <li>Execute the query</li>
                <li>Refresh this page</li>
              </ol>";
    }
} catch (PDOException $e) {
    echo "<p><span class='error'>✗ Connection Error:</span> " . $e->getMessage() . "</p>";
}
echo "</div>";

// 5. Test API endpoints
echo "<div class='card'><h2>5. API Endpoints Test</h2>";
echo "<p>Test the following endpoints:</p>";
echo "<ul>";
echo "<li><a href='INDEX.php?action=getAllRecipes' target='_blank'>GET /INDEX.php?action=getAllRecipes</a></li>";
echo "<li><a href='INDEX.php?action=getAllIngredients' target='_blank'>GET /INDEX.php?action=getAllIngredients</a></li>";
echo "</ul>";
echo "</div>";

// 6. Files check
echo "<div class='card'><h2>6. Required Files</h2>";
$files = [
    'INDEX.php',
    'config.php',
    'DATABASE/Database.php',
    'MODEL/Recipe.php',
    'MODEL/Ingredient.php',
    'CONTROLLER/RecipeC.php',
    'CONTROLLER/IngredientC.php',
    'VIEW/backoffice.html',
    'VIEW/frontoffice.html',
    'VIEW/js/backoffice.js',
    'VIEW/js/frontoffice.js',
    'kool_healthy.sql'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p><span class='success'>✓</span> $file</p>";
    } else {
        echo "<p><span class='error'>✗</span> $file - NOT FOUND</p>";
    }
}
echo "</div>";

echo "<div class='card'><h2>7. Quick Test</h2>";
echo "<p><a href='INDEX.php?view=backoffice' style='padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Backoffice →</a></p>";
echo "</div>";

echo "</body></html>";
?>
