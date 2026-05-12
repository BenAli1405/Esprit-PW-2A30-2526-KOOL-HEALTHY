<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateur = $authController->utilisateurConnecte();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($utilisateur) {
    if ($authController->estAdmin($utilisateur)) {
        header('Location: backoffice.php');
    } else {
        header('Location: home.php');
    }
    exit();
}
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
    <a class="page-logo" href="home.php" aria-label="Kool Healthy">
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

            <?php if ($success === 'password_reset'): ?>
                <div class="alert success">Mot de passe reinitialise avec succes. Vous pouvez vous connecter.</div>
            <?php endif; ?>

            <?php if ($error === 'google_not_configured'): ?>
                <div class="alert error">Connexion Google indisponible. Configuration OAuth manquante.</div>
            <?php endif; ?>

            <?php if (strpos($error, 'google_') === 0): ?>
                <div class="alert error">Connexion Google echouee. Veuillez reessayer.</div>
            <?php endif; ?>

            <section class="auth-box">
                <form method="POST" action="../CONTROLLER/AuthController.php?action=login" id="loginForm" novalidate>
                    <h2>Se connecter</h2>
                    <input type="text" name="nom" placeholder="Nom">
                    <input type="password" name="mot_de_passe" placeholder="Mot de passe">
                    <span id="loginError" class="password-error" style="display:none;color:red;font-size:0.9rem;margin-bottom:10px;"></span>
                    <button type="submit">Se connecter</button>
                    <div class="oauth-divider"><span>ou</span></div>
                    <a class="google-btn" href="../CONTROLLER/AuthController.php?action=google_login">Continuer avec Google</a>
                    <p class="switch-link">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                    <p class="switch-link">Mot de passe oublie ? <a href="forgot-password.php">Reinitialiser</a></p>
                </form>
            </section>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const loginError = document.getElementById('loginError');

            if (!loginForm || !loginError) {
                return;
            }

            loginForm.addEventListener('submit', function (event) {
                const nom = (loginForm.querySelector('input[name="nom"]')?.value || '').trim();
                const motDePasse = loginForm.querySelector('input[name="mot_de_passe"]')?.value || '';

                if (nom === '' || motDePasse === '') {
                    event.preventDefault();
                    loginError.textContent = 'Nom et mot de passe sont obligatoires.';
                    loginError.style.display = 'block';
                    return;
                }

                loginError.style.display = 'none';
            });
        });
    </script>
</body>
</html>
