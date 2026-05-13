<?php
// Test de connexion à la base de données

echo "<h1>Test de Connexion - Diagnostic</h1>";
echo "<hr>";

// Configuration
$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$password = '';
$database = 'projetweb';

echo "<h2>Configuration</h2>";
echo "Host: " . $host . "<br>";
echo "Port: " . $port . "<br>";
echo "User: " . $user . "<br>";
echo "Database: " . $database . "<br>";
echo "<hr>";

// Tentative 1: Connexion directe
echo "<h2>Tentative 1 - Connexion directe sans base de données</h2>";
try {
    $pdo = new PDO(
        'mysql:host=' . $host . ';port=' . $port,
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<span style='color: green;'>✓ Connexion réussie au serveur MySQL!</span><br>";
    
    // Créer la base de données si elle n'existe pas
    echo "<h2>Tentative 2 - Création/Sélection de la base de données</h2>";
    try {
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        echo "<span style='color: green;'>✓ Base de données créée/vérifiée!</span><br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Erreur: " . $e->getMessage() . "</span><br>";
    }
    
    // Tentative 3: Connexion avec la base de données
    echo "<h2>Tentative 3 - Connexion avec la base de données</h2>";
    try {
        $pdo2 = new PDO(
            'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4',
            $user,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<span style='color: green;'>✓ Connexion à la base de données réussie!</span><br>";
        
        // Vérifier les tables
        $tables = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables trouvées: " . count($tables) . "<br>";
        if (count($tables) > 0) {
            echo "Tables: " . implode(', ', $tables) . "<br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Erreur: " . $e->getMessage() . "</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erreur de connexion au serveur MySQL</span><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "<br><strong>Solutions:</strong><br>";
    echo "1. Vérifiez que XAMPP est lancé (Apache ET MySQL)<br>";
    echo "2. Ouvrez le panneau de contrôle XAMPP et vérifiez les ports<br>";
    echo "3. Si MySQL utilise un autre port, modifiez config.php<br>";
}

echo "<hr>";
echo "<a href='/Gamification/'>Retour à l'accueil</a>";
?>
