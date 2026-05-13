<?php
// Script pour créer un compte admin temporairement
require_once 'config.php';

$db = config::getConnexion();

// Données admin
$nom = 'Admin';
$email = 'admin@koolhealthy.com';
$mot_de_passe = password_hash('admin123', PASSWORD_BCRYPT);
$role = 'admin';

try {
    // Vérifier si l'admin existe déjà
    $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND role = 'admin' LIMIT 1");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        echo "✅ Compte admin existe déjà: $email<br>";
        $user = $check->fetch();
        echo "ID: " . $user['id'] . "<br>";
    } else {
        // Créer l'admin
        $insert = $db->prepare(
            "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $insert->execute([$nom, $email, $mot_de_passe, $role]);
        $adminId = $db->lastInsertId();
        echo "✅ Compte admin créé avec succès!<br>";
        echo "Email: $email<br>";
        echo "Mot de passe: admin123<br>";
        echo "ID: $adminId<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
