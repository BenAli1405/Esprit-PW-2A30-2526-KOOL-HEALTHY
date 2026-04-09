<?php
// Script d'initialisation: creer la base de donnees et les tables
$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$password = '';

try {
    // Connexion sans base de donnees pour creer recettes_db
    $pdo = new PDO('mysql:host=' . $host . ';port=' . $port, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creer la base de donnees
    $sql = file_get_contents(__DIR__ . '/recettes_db.sql');
    
    // Creer la base de donnees
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `recettes_db` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    
    // Selectionner la base de donnees
    $pdo->exec('USE `recettes_db`');
    
    // Executer le fichier SQL
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Base de donnees et tables creees avec succes!<br>";
    echo '<a href="index.php">Aller a l\'accueil</a>';
} catch (PDOException $e) {
    die('Erreur lors de l\'initialisation: ' . $e->getMessage());
}
?>
