<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateur = $authController->utilisateurConnecte();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kool Healthy - Se connecter</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>
<body>
    <a class="page-logo" href="index.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
    </a>
    <main class="auth-page">
        <section class="auth-card">
            <?php if ($success === 'register'): ?>
                <div class="alert success">Inscription réussie. Vous pouvez maintenant vous connecter.</div>
            <?php endif; ?>

            <?php if ($error === 'login'): ?>
                <div class="alert error">Nom ou mot de passe incorrect.</div>
            <?php endif; ?>

            <?php if ($error === 'register'): ?>
                <div class="alert error">Nom ou email déjà utilisé.</div>
            <?php endif; ?>

            <?php if ($error === 'password_mismatch'): ?>
                <div class="alert error">Les mots de passe ne correspondent pas.</div>
            <?php endif; ?>

            <?php if ($utilisateur): ?>
                <div class="logged-box">
                    <h2>Bonjour <?php echo htmlspecialchars($utilisateur['nom']); ?></h2>
                    <p><?php echo htmlspecialchars($utilisateur['email']); ?></p>
                    <a class="btn-link" href="../CONTROLLER/AuthController.php?action=logout">Se déconnecter</a>
                    <a class="btn-link secondary" href="index.php">Aller au fil des recettes</a>
                </div>
            <?php else: ?>
                <section class="auth-box">
                    <form method="POST" action="../CONTROLLER/AuthController.php?action=login">
                        <h2>Se connecter</h2>
                        <input type="text" name="nom" placeholder="Nom" required>
                        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
                        <button type="submit">Se connecter</button>
                        <p class="switch-link">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                        <p class="switch-link">Mot de passe oublié ? <a href="#">Réinitialiser</a></p>
                    </form>
                </section>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
